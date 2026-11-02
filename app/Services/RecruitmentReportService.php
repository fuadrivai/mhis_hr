<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface RecruitmentReportService
{
    /**
     * Get 5 core KPI summary cards for the recruitment dashboard.
     */
    public function getDashboardSummary(User $user, array $filters = []): array;

    /**
     * Get candidate funnel pipeline metrics per stage with percentage conversion.
     */
    public function getFunnelData(User $user, array $filters = []): array;

    /**
     * Get urgent action items:
     * - Pending interview evaluations > X days
     * - Pending offer approvals > X days
     * - Stale candidates with no activity > 30 days
     */
    public function getActionRequiredItems(User $user, array $filters = []): array;

    /**
     * Get Time-in-stage (average days in each stage) and Time-to-hire metrics.
     */
    public function getTimeInStageAndTimeToHire(User $user, array $filters = []): array;

    /**
     * Get Source Effectiveness (applied, shortlisted, hired, conversion rate per source).
     */
    public function getSourceEffectiveness(User $user, array $filters = []): array;

    /**
     * Get Offer Acceptance Rate and reasons for decline.
     */
    public function getOfferAcceptanceReport(User $user, array $filters = []): array;

    /**
     * Get rejection reason distribution from master reasons.
     */
    public function getRejectionDistribution(User $user, array $filters = []): array;

    /**
     * Get Headcount Fill Rate per opening/branch and opening aging.
     */
    public function getHeadcountFillRateAndAging(User $user, array $filters = []): array;

    /**
     * Get AI CV Match score distribution vs final hiring outcomes.
     */
    public function getAiScoreDistribution(User $user, array $filters = []): array;

    /**
     * Export reports to CSV with UTF-8 BOM, formula injection protection,
     * and strict branch/salary visibility scoping.
     */
    public function exportCsv(User $user, string $type, array $filters = []): StreamedResponse;

    /**
     * Get accessible filter options (branches and job openings) for the authenticated user.
     */
    public function getFilterOptions(User $user): array;
}

