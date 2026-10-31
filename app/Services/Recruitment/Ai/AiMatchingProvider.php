<?php

namespace App\Services\Recruitment\Ai;

interface AiMatchingProvider
{
    /**
     * Evaluate a candidate's CV against a set of job requirements.
     *
     * @param string $redactedCv Redacted text enclosed in <untrusted_cv_text>.
     * @param array $requirements Array of requirement details ['id', 'text', 'category', 'is_mandatory', 'weight'].
     * @param string $promptVersion Prompt version (default: 'v1').
     * @return array Decoded response ['summary', 'red_flags', 'interview_questions', 'requirements'].
     */
    public function matchCv(string $redactedCv, array $requirements, string $promptVersion = 'v1'): array;

    /**
     * Extract identity details from raw CV text for quick form auto-filling.
     *
     * @param string $rawCvText
     * @return array ['full_name' => ?string, 'email' => ?string, 'phone' => ?string, 'current_city' => ?string]
     */
    public function parseCvIdentity(string $rawCvText): array;

    /**
     * Extract text from a visual/scanned document or image via AI Vision OCR.
     *
     * @param string $filePath Absolute path to PDF or image file.
     * @return string Plain extracted text.
     */
    public function extractTextFromVisualDocument(string $filePath): string;

    /**
     * Return provider name.
     */
    public function getProviderName(): string;

    /**
     * Return model name.
     */
    public function getModelName(): string;
}

