<?php

namespace App\Services\Implement;

use App\Models\ApplicationAssessment;
use App\Models\ApplicationOnboarding;
use App\Models\ApplicationRejection;
use App\Models\ApplicationStageHistory;
use App\Models\Branch;
use App\Models\CandidateApplication;
use App\Models\CvMatchResult;
use App\Models\InterviewEvaluation;
use App\Models\JobOpening;
use App\Models\Offer;
use App\Models\RejectionReason;
use App\Models\User;
use App\Services\RecruitmentReportService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecruitmentReportImplement implements RecruitmentReportService
{
    public const PIPELINE_STAGES = [
        'applied'          => ['label' => 'Applied', 'color' => '#3498db'],
        'screening'        => ['label' => 'Screening', 'color' => '#9b59b6'],
        'psychotest'       => ['label' => 'Psychotest', 'color' => '#e67e22'],
        'interview'        => ['label' => 'Interview', 'color' => '#1abc9c'],
        'offer'            => ['label' => 'Offer', 'color' => '#f39c12'],
        'contract_signing' => ['label' => 'Contract Signing', 'color' => '#2980b9'],
        'joined'           => ['label' => 'Joined', 'color' => '#27ae60'],
        'rejected'         => ['label' => 'Rejected', 'color' => '#e74c3c'],
        'withdrawn'        => ['label' => 'Withdrawn', 'color' => '#95a5a6'],
    ];

    public function getDashboardSummary(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $cacheKey = 'recruitment_dash_summary_' . $user->id . '_' . md5(json_encode($filters));
        $ttl = (int) config('recruitment.dashboard_cache_ttl', 60);

        if (!empty($filters['refresh'])) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($user, $filters) {
            $branchId = $this->effectiveBranchId($user, $filters);
            $openingId = !empty($filters['job_opening_id']) ? (int) $filters['job_opening_id'] : null;

            // 1. Active Openings
            $activeOpeningsQuery = JobOpening::query()->where('status', 'open');
            if ($branchId !== null) {
                $activeOpeningsQuery->where('branch_id', $branchId);
            }
            if ($openingId !== null) {
                $activeOpeningsQuery->where('id', $openingId);
            }
            $activeOpeningsCount = $activeOpeningsQuery->count();

            // 2. Active Candidates (not in terminal stages: rejected, withdrawn, joined)
            $activeCandidatesQuery = CandidateApplication::query()
                ->whereNotIn('stage', ['rejected', 'withdrawn', 'joined']);
            $this->applyApplicationScope($activeCandidatesQuery, $user, $filters);
            $activeCandidatesCount = $activeCandidatesQuery->count();

            // 3. Offers pending approval
            $pendingOffersQuery = Offer::query()->where('status', 'pending_approval')
                ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));
            $pendingOffersCount = $pendingOffersQuery->count();

            // 4. Candidates awaiting interview evaluation
            $awaitingEvalQuery = CandidateApplication::query()
                ->where('stage', 'interview')
                ->where(function ($query) {
                    $query->where('stage_substatus', 'awaiting_evaluation')
                        ->orWhereNull('stage_substatus')
                        ->orWhereHas('interviewRounds.evaluations', function ($sub) {
                            $sub->where('status', 'draft');
                        });
                });
            $this->applyApplicationScope($awaitingEvalQuery, $user, $filters);
            $awaitingInterviewCount = $awaitingEvalQuery->count();

            // 5. Joining in next 30 days
            $today = Carbon::today()->toDateString();
            $next30Days = Carbon::today()->addDays(30)->toDateString();
            $upcomingJoiningsQuery = ApplicationOnboarding::query()
                ->whereIn('join_status', ['pending', 'postponed', 'joined'])
                ->whereBetween('join_date', [$today, $next30Days])
                ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));
            $upcomingJoiningsCount = $upcomingJoiningsQuery->count();

            return [
                'active_openings'          => $activeOpeningsCount,
                'active_candidates'        => $activeCandidatesCount,
                'pending_offers'           => $pendingOffersCount,
                'awaiting_evaluations'     => $awaitingInterviewCount,
                'upcoming_joinings_30d'    => $upcomingJoiningsCount,
            ];
        });
    }

    public function getFunnelData(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $query = CandidateApplication::query();
        $this->applyApplicationScope($query, $user, $filters);

        $totalApplications = (clone $query)->count();

        $stageCounts = (clone $query)
            ->select('stage', DB::raw('count(*) as count'))
            ->groupBy('stage')
            ->pluck('count', 'stage')
            ->all();

        $stages = [];
        foreach (self::PIPELINE_STAGES as $key => $meta) {
            $count = (int) ($stageCounts[$key] ?? 0);
            $percentage = $totalApplications > 0 ? round(($count / $totalApplications) * 100, 1) : 0;
            $stages[$key] = [
                'stage'      => $key,
                'label'      => $meta['label'],
                'color'      => $meta['color'],
                'count'      => $count,
                'percentage' => $percentage,
            ];
        }

        // Additional opening metadata if filtered by opening
        $selectedOpening = null;
        if (!empty($filters['job_opening_id'])) {
            $selectedOpening = JobOpening::with('branch')->find($filters['job_opening_id']);
        }

        return [
            'total_applications' => $totalApplications,
            'stages'             => $stages,
            'selected_opening'   => $selectedOpening ? [
                'id'           => $selectedOpening->id,
                'title'        => $selectedOpening->title,
                'headcount'    => $selectedOpening->headcount,
                'filled_count' => $selectedOpening->filled_count,
                'status'       => $selectedOpening->status,
                'branch'       => $selectedOpening->branch?->name,
            ] : null,
        ];
    }

    public function getActionRequiredItems(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $evalDays = (int) config('recruitment.action_required.interview_evaluation_days', 3);
        $offerDays = (int) config('recruitment.action_required.offer_pending_days', 3);
        $inactiveDays = (int) config('recruitment.action_required.inactive_candidate_days', 30);

        // 1. Pending interview evaluations older than X days
        $evalThreshold = Carbon::now()->subDays($evalDays);
        $pendingEvaluations = InterviewEvaluation::query()
            ->with([
                'interviewRound.candidateApplication.candidate',
                'interviewRound.candidateApplication.jobOpening',
                'interviewerEmployee.personal',
                'interviewerEmployee.user',
            ])
            ->where('status', 'draft')
            ->where('created_at', '<=', $evalThreshold)
            ->whereHas('interviewRound.candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters))
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(function (InterviewEvaluation $eval) {
                $app = $eval->interviewRound?->candidateApplication;
                $candidate = $app?->candidate;
                $opening = $app?->jobOpening;
                $days = Carbon::parse($eval->created_at)->diffInDays(now());
                $interviewerName = $eval->interviewerEmployee?->personal?->fullname
                    ?? $eval->interviewerEmployee?->user?->name
                    ?? 'Assigned Interviewer';

                return [
                    'id'               => $eval->id,
                    'type'             => 'evaluation',
                    'title'            => 'Interview evaluation pending',
                    'candidate_name'   => $candidate?->full_name ?? 'Unknown Candidate',
                    'candidate_id'     => $candidate?->id,
                    'application_id'   => $app?->id,
                    'job_title'        => $opening?->title ?: ($app?->position_applied ?? 'General'),
                    'interviewer_name' => $interviewerName,
                    'round_no'         => $eval->interviewRound?->round_no ?? 1,
                    'round_type'       => $eval->interviewRound?->round_type ?? 'hr',
                    'days_pending'     => $days,
                    'created_at'       => $eval->created_at?->format('d M Y H:i'),
                    'url'              => route('recruitment.candidates.show', ['candidate' => $candidate?->id ?? 1]),
                ];
            });

        // 2. Pending offer approvals older than X days
        $offerThreshold = Carbon::now()->subDays($offerDays);
        $pendingOffers = Offer::query()
            ->with([
                'candidateApplication.candidate',
                'candidateApplication.jobOpening',
                'steps.approverEmployee.personal',
                'steps.approverEmployee.user',
            ])
            ->where('status', 'pending_approval')
            ->where('created_at', '<=', $offerThreshold)
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters))
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(function (Offer $offer) {
                $app = $offer->candidateApplication;
                $candidate = $app?->candidate;
                $days = Carbon::parse($offer->created_at)->diffInDays(now());

                // Find active pending approver
                $pendingStep = $offer->steps->where('status', 'pending')->first();
                $approverEmp = $pendingStep?->approverEmployee;
                $approverName = $approverEmp?->personal?->fullname ?? $approverEmp?->user?->name ?? 'Approver';

                return [
                    'id'             => $offer->id,
                    'type'           => 'offer',
                    'title'          => 'Offer approval pending',
                    'candidate_name' => $candidate?->full_name ?? 'Unknown Candidate',
                    'candidate_id'   => $candidate?->id,
                    'application_id' => $app?->id,
                    'job_title'      => $offer->position_title,
                    'approver_name'  => $approverName,
                    'days_pending'   => $days,
                    'created_at'     => $offer->created_at?->format('d M Y H:i'),
                    'url'            => route('recruitment.candidates.show', ['candidate' => $candidate?->id ?? 1]),
                ];
            });

        // 3. Inactive candidates > 30 days
        $inactiveThreshold = Carbon::now()->subDays($inactiveDays);
        $inactiveCandidates = CandidateApplication::query()
            ->with(['candidate', 'jobOpening', 'branch'])
            ->whereNotIn('stage', ['rejected', 'withdrawn', 'joined'])
            ->where('last_activity_at', '<=', $inactiveThreshold)
            ->tap(fn ($q) => $this->applyApplicationScope($q, $user, $filters))
            ->orderBy('last_activity_at')
            ->limit(20)
            ->get()
            ->map(function (CandidateApplication $app) {
                $candidate = $app->candidate;
                $days = $app->last_activity_at ? Carbon::parse($app->last_activity_at)->diffInDays(now()) : 30;

                return [
                    'id'               => $app->id,
                    'type'             => 'inactive',
                    'title'            => 'No candidate activity',
                    'candidate_name'   => $candidate?->full_name ?? 'Unknown Candidate',
                    'candidate_id'     => $candidate?->id,
                    'application_id'   => $app->id,
                    'job_title'        => $app->jobOpening?->title ?: ($app->position_applied ?? 'General'),
                    'stage'            => self::PIPELINE_STAGES[$app->stage]['label'] ?? $app->stage,
                    'days_pending'     => $days,
                    'last_activity_at' => $app->last_activity_at?->format('d M Y'),
                    'url'              => route('recruitment.candidates.show', ['candidate' => $candidate?->id ?? 1]),
                ];
            });

        return [
            'thresholds' => [
                'eval_days'     => $evalDays,
                'offer_days'    => $offerDays,
                'inactive_days' => $inactiveDays,
            ],
            'pending_evaluations' => $pendingEvaluations,
            'pending_offers'      => $pendingOffers,
            'inactive_candidates' => $inactiveCandidates,
            'total_actions'       => $pendingEvaluations->count() + $pendingOffers->count() + $inactiveCandidates->count(),
        ];
    }

    public function getTimeInStageAndTimeToHire(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        // Fetch applications matching filters
        $appQuery = CandidateApplication::query();
        $this->applyApplicationScope($appQuery, $user, $filters);
        $applicationIds = $appQuery->pluck('id');

        if ($applicationIds->isEmpty()) {
            return [
                'stages_duration' => [],
                'time_to_hire'    => [
                    'avg_days'    => 0,
                    'min_days'    => 0,
                    'max_days'    => 0,
                    'total_hired' => 0,
                ],
                'hired_breakdown' => [],
            ];
        }

        // Fetch stage histories ordered by candidate_application_id and changed_at
        $histories = ApplicationStageHistory::query()
            ->whereIn('candidate_application_id', $applicationIds)
            ->orderBy('candidate_application_id')
            ->orderBy('changed_at')
            ->get()
            ->groupBy('candidate_application_id');

        $stageDurations = [];
        $timeToHireDays = [];
        $hiredBreakdown = [];

        foreach ($histories as $appId => $records) {
            $count = $records->count();
            for ($i = 0; $i < $count; $i++) {
                $current = $records[$i];
                $next = $records[$i + 1] ?? null;

                $start = Carbon::parse($current->changed_at);
                $end = $next ? Carbon::parse($next->changed_at) : Carbon::now();
                $diffDays = max(0, round($start->floatDiffInDays($end), 1));

                $stage = $current->to_stage;
                if (!isset($stageDurations[$stage])) {
                    $stageDurations[$stage] = [];
                }
                $stageDurations[$stage][] = $diffDays;

                // Time-to-hire check: reached stage 'joined'
                if ($current->to_stage === 'joined') {
                    $firstHistory = $records->first();
                    $appliedTime = Carbon::parse($firstHistory->changed_at);
                    $hiredTime = Carbon::parse($current->changed_at);
                    $totalDays = max(0, round($appliedTime->floatDiffInDays($hiredTime), 1));
                    $timeToHireDays[] = $totalDays;

                    $app = CandidateApplication::with(['candidate', 'jobOpening', 'branch'])->find($appId);
                    if ($app) {
                        $hiredBreakdown[] = [
                            'candidate_name' => $app->candidate?->full_name,
                            'position_title' => $app->jobOpening?->title ?: ($app->position_applied ?? 'N/A'),
                            'branch_name'    => $app->branch?->name ?? 'Head Office',
                            'applied_date'   => $app->applied_at?->format('d M Y') ?? $appliedTime->format('d M Y'),
                            'joined_date'    => $hiredTime->format('d M Y'),
                            'days_to_hire'   => $totalDays,
                        ];
                    }
                }
            }
        }

        $stagesSummary = [];
        foreach (self::PIPELINE_STAGES as $stageKey => $meta) {
            $durations = $stageDurations[$stageKey] ?? [];
            $avg = count($durations) > 0 ? round(array_sum($durations) / count($durations), 1) : 0;
            $stagesSummary[$stageKey] = [
                'stage'        => $stageKey,
                'label'        => $meta['label'],
                'color'        => $meta['color'],
                'avg_days'     => $avg,
                'sample_count' => count($durations),
            ];
        }

        $avgTimeToHire = count($timeToHireDays) > 0 ? round(array_sum($timeToHireDays) / count($timeToHireDays), 1) : 0;
        $minTimeToHire = count($timeToHireDays) > 0 ? min($timeToHireDays) : 0;
        $maxTimeToHire = count($timeToHireDays) > 0 ? max($timeToHireDays) : 0;

        return [
            'stages_duration' => $stagesSummary,
            'time_to_hire'    => [
                'avg_days'    => $avgTimeToHire,
                'min_days'    => $minTimeToHire,
                'max_days'    => $maxTimeToHire,
                'total_hired' => count($timeToHireDays),
            ],
            'hired_breakdown' => $hiredBreakdown,
        ];
    }

    public function getSourceEffectiveness(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $query = CandidateApplication::query();
        $this->applyApplicationScope($query, $user, $filters);

        $records = (clone $query)
            ->select('source', 'stage', 'status')
            ->get();

        $grouped = $records->groupBy(fn ($item) => trim($item->source ?: 'Unknown'));

        $result = [];
        $totalAll = $records->count();

        foreach ($grouped as $sourceName => $items) {
            $applied = $items->count();
            $shortlisted = $items->whereNotIn('stage', ['applied', 'withdrawn'])->count();
            $hired = $items->filter(fn ($i) => $i->stage === 'joined' || $i->status === 'hired')->count();
            $conversionRate = $applied > 0 ? round(($hired / $applied) * 100, 1) : 0;
            $shortlistRate = $applied > 0 ? round(($shortlisted / $applied) * 100, 1) : 0;
            $shareOfTotal = $totalAll > 0 ? round(($applied / $totalAll) * 100, 1) : 0;

            $result[] = [
                'source'          => $sourceName,
                'applied'         => $applied,
                'shortlisted'     => $shortlisted,
                'hired'           => $hired,
                'conversion_rate' => $conversionRate,
                'shortlist_rate'  => $shortlistRate,
                'share_of_total'  => $shareOfTotal,
            ];
        }

        // Sort by applied descending
        usort($result, fn ($a, $b) => $b['applied'] <=> $a['applied']);

        return [
            'total_applications' => $totalAll,
            'sources'            => $result,
        ];
    }

    public function getOfferAcceptanceReport(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $offersQuery = Offer::query()
            ->with(['candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $offers = $offersQuery->get();

        $totalOffers = $offers->count();
        $sentOffers = $offers->filter(fn ($o) => !empty($o->sent_at) || in_array($o->status, ['sent', 'accepted', 'declined']))->count();
        $acceptedOffers = $offers->where('status', 'accepted')->count();
        $declinedOffers = $offers->where('status', 'declined')->count();
        $pendingApproval = $offers->where('status', 'pending_approval')->count();
        $draftOffers = $offers->where('status', 'draft')->count();

        $decidedOffers = $acceptedOffers + $declinedOffers;
        $acceptanceRate = $decidedOffers > 0 ? round(($acceptedOffers / $decidedOffers) * 100, 1) : 0;

        // Group decline reasons
        $declinedBreakdown = [];
        foreach ($offers->where('status', 'declined') as $declined) {
            $reason = trim($declined->decline_reason ?: 'Reason not specified');
            $declinedBreakdown[$reason] = ($declinedBreakdown[$reason] ?? 0) + 1;
        }

        arsort($declinedBreakdown);

        return [
            'total_offers'       => $totalOffers,
            'sent_offers'        => $sentOffers,
            'accepted_offers'    => $acceptedOffers,
            'declined_offers'    => $declinedOffers,
            'pending_approval'   => $pendingApproval,
            'draft_offers'       => $draftOffers,
            'acceptance_rate'    => $acceptanceRate,
            'decline_breakdown'  => $declinedBreakdown,
            'recent_offers'      => $offers->sortByDesc('created_at')->take(10)->values(),
        ];
    }

    public function getRejectionDistribution(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $rejectionsQuery = ApplicationRejection::query()
            ->with(['rejectionReason', 'candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $rejections = $rejectionsQuery->get();
        $totalRejections = $rejections->count();

        // Group by reason
        $byReason = [];
        $byStage = [];

        foreach ($rejections as $rej) {
            $reasonName = $rej->rejectionReason?->name ?? 'Other / Unspecified';
            $byReason[$reasonName] = ($byReason[$reasonName] ?? 0) + 1;

            $stageName = self::PIPELINE_STAGES[$rej->rejected_at_stage]['label'] ?? $rej->rejected_at_stage;
            $byStage[$stageName] = ($byStage[$stageName] ?? 0) + 1;
        }

        arsort($byReason);
        arsort($byStage);

        $reasonList = [];
        foreach ($byReason as $name => $count) {
            $reasonList[] = [
                'name'       => $name,
                'count'      => $count,
                'percentage' => $totalRejections > 0 ? round(($count / $totalRejections) * 100, 1) : 0,
            ];
        }

        $stageList = [];
        foreach ($byStage as $name => $count) {
            $stageList[] = [
                'stage'      => $name,
                'count'      => $count,
                'percentage' => $totalRejections > 0 ? round(($count / $totalRejections) * 100, 1) : 0,
            ];
        }

        return [
            'total_rejections' => $totalRejections,
            'by_reason'        => $reasonList,
            'by_stage'         => $stageList,
            'recent_rejections' => $rejections->sortByDesc('created_at')->take(15)->values(),
        ];
    }

    public function getHeadcountFillRateAndAging(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $branchId = $this->effectiveBranchId($user, $filters);
        $openingId = !empty($filters['job_opening_id']) ? (int) $filters['job_opening_id'] : null;

        $query = JobOpening::query()->with('branch');

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }
        if ($openingId !== null) {
            $query->where('id', $openingId);
        }

        $openings = $query->orderBy('status')->orderByDesc('opened_at')->get();

        $totalOpenings = $openings->count();
        $totalHeadcount = $openings->sum('headcount');
        $totalFilled = 0;

        $openingRows = $openings->map(function (JobOpening $op) use (&$totalFilled) {
            $filled = $op->filled_count;
            $totalFilled += $filled;
            $rate = $op->headcount > 0 ? min(100, round(($filled / $op->headcount) * 100, 1)) : 0;

            $openDate = Carbon::parse($op->opened_at);
            $endDate = $op->closes_at ? Carbon::parse($op->closes_at) : Carbon::now();
            $agingDays = max(0, $openDate->diffInDays($endDate));

            return [
                'id'            => $op->id,
                'title'         => $op->title,
                'branch'        => $op->branch?->name ?? 'Head Office',
                'headcount'     => $op->headcount,
                'filled'        => $filled,
                'fill_rate'     => $rate,
                'status'        => $op->status,
                'opened_at'     => $op->opened_at?->format('d M Y'),
                'closes_at'     => $op->closes_at?->format('d M Y'),
                'aging_days'    => $agingDays,
            ];
        });

        $overallFillRate = $totalHeadcount > 0 ? min(100, round(($totalFilled / $totalHeadcount) * 100, 1)) : 0;

        return [
            'total_openings'    => $totalOpenings,
            'total_headcount'   => $totalHeadcount,
            'total_filled'      => $totalFilled,
            'overall_fill_rate' => $overallFillRate,
            'openings'          => $openingRows,
        ];
    }

    public function getAiScoreDistribution(User $user, array $filters = []): array
    {
        $this->authorizeRecruitment($user);

        $resultsQuery = CvMatchResult::query()
            ->with(['candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->where('status', 'completed')
            ->whereNotNull('total_score')
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $results = $resultsQuery->get();

        $buckets = [
            '< 60'    => ['range' => '< 60', 'min' => 0, 'max' => 59, 'hired' => 0, 'in_progress' => 0, 'rejected' => 0, 'total' => 0],
            '60 - 69' => ['range' => '60 - 69', 'min' => 60, 'max' => 69, 'hired' => 0, 'in_progress' => 0, 'rejected' => 0, 'total' => 0],
            '70 - 79' => ['range' => '70 - 79', 'min' => 70, 'max' => 79, 'hired' => 0, 'in_progress' => 0, 'rejected' => 0, 'total' => 0],
            '80 - 89' => ['range' => '80 - 89', 'min' => 80, 'max' => 89, 'hired' => 0, 'in_progress' => 0, 'rejected' => 0, 'total' => 0],
            '90 - 100'=> ['range' => '90 - 100', 'min' => 90, 'max' => 100, 'hired' => 0, 'in_progress' => 0, 'rejected' => 0, 'total' => 0],
        ];

        $hiredScores = [];
        $rejectedScores = [];

        foreach ($results as $res) {
            $score = (int) $res->total_score;
            $app = $res->candidateApplication;
            $stage = $app?->stage ?? 'applied';

            $bucketKey = null;
            if ($score < 60) {
                $bucketKey = '< 60';
            } elseif ($score <= 69) {
                $bucketKey = '60 - 69';
            } elseif ($score <= 79) {
                $bucketKey = '70 - 79';
            } elseif ($score <= 89) {
                $bucketKey = '80 - 89';
            } else {
                $bucketKey = '90 - 100';
            }

            $buckets[$bucketKey]['total']++;

            if ($stage === 'joined') {
                $buckets[$bucketKey]['hired']++;
                $hiredScores[] = $score;
            } elseif (in_array($stage, ['rejected', 'withdrawn'])) {
                $buckets[$bucketKey]['rejected']++;
                $rejectedScores[] = $score;
            } else {
                $buckets[$bucketKey]['in_progress']++;
            }
        }

        $avgHired = count($hiredScores) > 0 ? round(array_sum($hiredScores) / count($hiredScores), 1) : null;
        $avgRejected = count($rejectedScores) > 0 ? round(array_sum($rejectedScores) / count($rejectedScores), 1) : null;

        return [
            'total_scored' => $results->count(),
            'buckets'      => array_values($buckets),
            'avg_hired'    => $avgHired,
            'avg_rejected' => $avgRejected,
        ];
    }

    public function exportCsv(User $user, string $type, array $filters = []): StreamedResponse
    {
        $this->authorizeRecruitment($user);

        $filename = 'recruitment-' . $type . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($user, $type, $filters) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

            switch ($type) {
                case 'funnel':
                    $this->streamFunnelCsv($output, $user, $filters);
                    break;
                case 'time_to_hire':
                    $this->streamTimeToHireCsv($output, $user, $filters);
                    break;
                case 'sources':
                    $this->streamSourcesCsv($output, $user, $filters);
                    break;
                case 'offers':
                    $this->streamOffersCsv($output, $user, $filters);
                    break;
                case 'rejections':
                    $this->streamRejectionsCsv($output, $user, $filters);
                    break;
                case 'headcount':
                    $this->streamHeadcountCsv($output, $user, $filters);
                    break;
                case 'ai_scores':
                    $this->streamAiScoresCsv($output, $user, $filters);
                    break;
                default:
                    fputcsv($output, ['Error', 'Unsupported export type']);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function getFilterOptions(User $user): array
    {
        $this->authorizeRecruitment($user);

        $branchScopeId = $user->branchScopeId();

        $branchesQuery = Branch::where('is_active', true);
        if ($branchScopeId !== null) {
            $branchesQuery->where('id', $branchScopeId);
        }
        $branches = $branchesQuery->orderBy('name')->get(['id', 'name']);

        $openingsQuery = JobOpening::query();
        if ($branchScopeId !== null) {
            $openingsQuery->where('branch_id', $branchScopeId);
        }
        $openings = $openingsQuery->orderByDesc('created_at')->get(['id', 'title', 'branch_id']);

        return [
            'branches'      => $branches,
            'openings'      => $openings,
            'branchScopeId' => $branchScopeId,
        ];
    }

    private function streamFunnelCsv($output, User $user, array $filters): void
    {
        fputcsv($output, ['Pipeline Stage', 'Count', 'Percentage of Total']);
        $funnel = $this->getFunnelData($user, $filters);
        foreach ($funnel['stages'] as $st) {
            fputcsv($output, array_map($this->safeCsvValue(...), [
                $st['label'],
                $st['count'],
                $st['percentage'] . '%',
            ]));
        }
    }

    private function streamTimeToHireCsv($output, User $user, array $filters): void
    {
        fputcsv($output, ['Candidate Name', 'Job Opening', 'Branch', 'Applied Date', 'Joined Date', 'Days to Hire']);
        $report = $this->getTimeInStageAndTimeToHire($user, $filters);
        foreach ($report['hired_breakdown'] as $row) {
            fputcsv($output, array_map($this->safeCsvValue(...), [
                $row['candidate_name'],
                $row['position_title'],
                $row['branch_name'],
                $row['applied_date'],
                $row['joined_date'],
                $row['days_to_hire'],
            ]));
        }
    }

    private function streamSourcesCsv($output, User $user, array $filters): void
    {
        fputcsv($output, ['Source Channel', 'Total Applicants', 'Shortlisted', 'Hired', 'Conversion Rate', 'Share of Total']);
        $report = $this->getSourceEffectiveness($user, $filters);
        foreach ($report['sources'] as $row) {
            fputcsv($output, array_map($this->safeCsvValue(...), [
                $row['source'],
                $row['applied'],
                $row['shortlisted'],
                $row['hired'],
                $row['conversion_rate'] . '%',
                $row['share_of_total'] . '%',
            ]));
        }
    }

    private function streamOffersCsv($output, User $user, array $filters): void
    {
        $canViewSalary = $user->isRecruitmentAdministrator() || $user->isRecruitmentMonitor();

        fputcsv($output, [
            'Offer ID',
            'Candidate Name',
            'Job Opening',
            'Branch',
            'Status',
            'Proposed Start Date',
            'Basic Salary',
            'Decline Reason',
            'Created Date',
        ]);

        $offersQuery = Offer::query()
            ->with(['candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $offersQuery->chunk(200, function ($offers) use ($output, $canViewSalary) {
            foreach ($offers as $offer) {
                $app = $offer->candidateApplication;
                $candidate = $app?->candidate;

                $salaryText = $canViewSalary ? ((float) $offer->basic_salary) : '[Restricted]';

                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $offer->id,
                    $candidate?->full_name ?? 'N/A',
                    $offer->position_title,
                    $app?->branch?->name ?? 'Head Office',
                    ucfirst(str_replace('_', ' ', $offer->status)),
                    $offer->proposed_start_date?->format('Y-m-d') ?? '-',
                    $salaryText,
                    $offer->decline_reason ?? '-',
                    $offer->created_at?->format('Y-m-d H:i'),
                ]));
            }
        });
    }

    private function streamRejectionsCsv($output, User $user, array $filters): void
    {
        fputcsv($output, [
            'Candidate Name',
            'Job Opening',
            'Branch',
            'Rejection Reason',
            'Rejected At Stage',
            'Notes',
            'Rejected Date',
        ]);

        $query = ApplicationRejection::query()
            ->with(['rejectionReason', 'candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $query->chunk(200, function ($rejections) use ($output) {
            foreach ($rejections as $rej) {
                $app = $rej->candidateApplication;
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $app?->candidate?->full_name ?? 'N/A',
                    $app?->jobOpening?->title ?: ($app?->position_applied ?? 'General'),
                    $app?->branch?->name ?? 'Head Office',
                    $rej->rejectionReason?->name ?? 'N/A',
                    self::PIPELINE_STAGES[$rej->rejected_at_stage]['label'] ?? $rej->rejected_at_stage,
                    $rej->notes ?? '-',
                    $rej->created_at?->format('Y-m-d H:i'),
                ]));
            }
        });
    }

    private function streamHeadcountCsv($output, User $user, array $filters): void
    {
        fputcsv($output, ['Job Opening Title', 'Branch', 'Headcount Target', 'Filled Count', 'Fill Rate', 'Status', 'Opened Date', 'Aging Days']);
        $report = $this->getHeadcountFillRateAndAging($user, $filters);
        foreach ($report['openings'] as $row) {
            fputcsv($output, array_map($this->safeCsvValue(...), [
                $row['title'],
                $row['branch'],
                $row['headcount'],
                $row['filled'],
                $row['fill_rate'] . '%',
                ucfirst($row['status']),
                $row['opened_at'],
                $row['aging_days'],
            ]));
        }
    }

    private function streamAiScoresCsv($output, User $user, array $filters): void
    {
        fputcsv($output, ['Candidate Name', 'Job Opening', 'Branch', 'AI Match Score', 'Missing Mandatory', 'Current Stage', 'Summary']);

        $query = CvMatchResult::query()
            ->with(['candidateApplication.candidate', 'candidateApplication.jobOpening', 'candidateApplication.branch'])
            ->where('status', 'completed')
            ->whereHas('candidateApplication', fn ($q) => $this->applyApplicationScope($q, $user, $filters));

        $query->chunk(200, function ($results) use ($output) {
            foreach ($results as $res) {
                $app = $res->candidateApplication;
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $app?->candidate?->full_name ?? 'N/A',
                    $app?->jobOpening?->title ?: ($app?->position_applied ?? 'General'),
                    $app?->branch?->name ?? 'Head Office',
                    $res->total_score ?? '0',
                    $res->mandatory_missing ? 'Yes' : 'No',
                    self::PIPELINE_STAGES[$app?->stage ?? '']['label'] ?? $app?->stage,
                    $res->summary ?? '-',
                ]));
            }
        });
    }

    private function applyApplicationScope(Builder $query, User $user, array $filters): void
    {
        $branchId = $this->effectiveBranchId($user, $filters);
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        if (!empty($filters['job_opening_id'])) {
            $query->where('job_opening_id', (int) $filters['job_opening_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('applied_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }

        if (!empty($filters['end_date'])) {
            $query->where('applied_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
    }

    private function effectiveBranchId(User $user, array $filters): ?int
    {
        $branchScope = $user->branchScopeId();
        if ($branchScope !== null) {
            return $branchScope;
        }

        return !empty($filters['branch_id']) ? (int) $filters['branch_id'] : null;
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function authorizeRecruitment(User $user): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('Recruitment access is not allowed.');
        }
    }
}

