<?php

namespace App\Services\Recruitment\Ai;

class CvScoreCalculator
{
    private int $mandatoryCap;

    public function __construct()
    {
        $this->mandatoryCap = (int) config('ai.mandatory_score_cap', 60);
    }

    /**
     * Compute total score and verify evidence against redacted CV text.
     *
     * @param array $aiRequirementResults Array of ['id', 'status', 'evidence', 'reason'] from LLM
     * @param array $requirementDefinitions Array of Requirement Detail models or arrays with ['id', 'is_mandatory', 'weight']
     * @param string $rawRedactedCvText The text of the CV that was sent to the AI
     * @return array ['total_score' => int, 'mandatory_missing' => bool, 'results' => array]
     */
    public function calculate(array $aiRequirementResults, array $requirementDefinitions, string $rawRedactedCvText): array
    {
        $defMap = [];
        foreach ($requirementDefinitions as $def) {
            $defMap[$def['id']] = $def;
        }

        $totalWeightedScore = 0.0;
        $totalWeight = 0;
        $mandatoryMissing = false;
        $processedResults = [];

        $normalizedCv = $this->normalizeString($rawRedactedCvText);

        foreach ($aiRequirementResults as $aiResult) {
            $reqId = $aiResult['id'];
            $def = $defMap[$reqId] ?? null;

            $weight = max(1, (int) ($def['weight'] ?? 3));
            $isMandatory = (bool) ($def['is_mandatory'] ?? false);
            $status = $aiResult['status'] ?? 'unknown';
            $evidence = trim((string) ($aiResult['evidence'] ?? ''));
            $reason = trim((string) ($aiResult['reason'] ?? ''));

            // Verify evidence in CV text
            $evidenceVerified = false;
            if ($evidence !== '') {
                $evidenceVerified = $this->verifyEvidenceInCv($evidence, $normalizedCv);
                // If evidence could not be verified in the actual CV, downgrade 'met' to 'partial'
                if (!$evidenceVerified && $status === 'met') {
                    $status = 'partial';
                    $reason .= ' (Downgraded: evidence quotation could not be verified in CV text)';
                }
            }

            // Check mandatory requirement missing
            if ($isMandatory && in_array($status, ['not_met'], true)) {
                $mandatoryMissing = true;
            }

            // Map status to numeric value: met=1.0, partial=0.5, not_met=0.0, unknown=0.0
            $value = match ($status) {
                'met'     => 1.0,
                'partial' => 0.5,
                default   => 0.0,
            };

            $totalWeightedScore += ($weight * $value);
            $totalWeight += $weight;

            $processedResults[] = [
                'id'                        => $reqId,
                'job_requirement_detail_id' => $reqId,
                'requirement_text'          => $def['requirement'] ?? ($def['text'] ?? ''),
                'category'                  => $def['category'] ?? 'other',
                'is_mandatory'              => $isMandatory,
                'weight'                    => $weight,
                'status'                    => $status,
                'evidence'                  => $evidence,
                'evidence_verified'         => $evidenceVerified,
                'reason'                    => $reason,
            ];
        }

        $computedScore = $totalWeight > 0
            ? (int) round(($totalWeightedScore / $totalWeight) * 100)
            : 0;

        // Apply score cap if mandatory requirements are not met
        if ($mandatoryMissing && $computedScore > $this->mandatoryCap) {
            $computedScore = $this->mandatoryCap;
        }

        return [
            'total_score'       => $computedScore,
            'mandatory_missing' => $mandatoryMissing,
            'results'           => $processedResults,
        ];
    }

    private function verifyEvidenceInCv(string $evidence, string $normalizedCv): bool
    {
        $normalizedEvidence = $this->normalizeString($evidence);
        if (mb_strlen($normalizedEvidence) < 5) {
            return false;
        }

        // Direct substring check
        if (str_contains($normalizedCv, $normalizedEvidence)) {
            return true;
        }

        // Substring chunk check: test if majority of words appear in sequence
        $words = array_filter(explode(' ', $normalizedEvidence), fn ($w) => strlen($w) >= 3);
        if (count($words) >= 3) {
            $chunk = implode(' ', array_slice($words, 0, min(5, count($words))));
            if (str_contains($normalizedCv, $chunk)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeString(string $text): string
    {
        // Lowercase and strip punctuation/symbols to facilitate robust matching
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}

