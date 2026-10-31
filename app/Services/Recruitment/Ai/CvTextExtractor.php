<?php

namespace App\Services\Recruitment\Ai;

interface CvTextExtractor
{
    /**
     * Extract raw text from a CV document (PDF, DOCX, DOC, TXT).
     *
     * @param string $filePath Absolute path to the file.
     * @return string Extracted plain text.
     * @throws \RuntimeException if file is unreadable or unsupported.
     */
    public function extractText(string $filePath): string;
}

