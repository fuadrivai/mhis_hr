<?php

namespace App\Services\Recruitment\Ai;

class PiiRedactor
{
    /**
     * Redact personally identifiable information (PII) from CV text.
     *
     * @param string $text
     * @param string|array|null $candidateName
     * @param string|null $candidateCode
     * @return string
     */
    public function redact(string $text, string|array|null $candidateName = null, ?string $candidateCode = null): string
    {
        $code = $candidateCode ?: 'CAN-CANDIDATE';
        $redacted = $text;

        // 1. Redact Email addresses FIRST so names inside emails don't break email regex
        $redacted = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/u', '[REDACTED_EMAIL]', $redacted);

        // 2. Redact Phone numbers (+62..., 08..., international, formatted digits)
        $redacted = preg_replace(
            '/(?:\+?62|08|\b02\d|\b\(?\+?[0-9]{1,3}\)?)[-.\s]?(?:\(0?\d{2,4}\)[-.\s]?)?\d{2,4}[-.\s]?\d{3,5}[-.\s]?\d{2,5}\b/',
            '[REDACTED_PHONE]',
            $redacted
        );

        // 3. Redact URLs (LinkedIn, GitHub, Portfolios, generic URLs)
        $redacted = preg_replace(
            '/(?:https?:\/\/|www\.)[^\s<>"\'{}|\\^`]+|(?:linkedin\.com\/in\/|github\.com\/)[^\s<>"\'{}|\\^`]+/iu',
            '[REDACTED_URL]',
            $redacted
        );

        // 4. Redact Identity numbers (16-digit NIK, NPWP 15/16-digits, passport patterns)
        $redacted = preg_replace('/\b\d{16}\b/', '[REDACTED_NIK]', $redacted);
        $redacted = preg_replace('/\b\d{2}\.?\d{3}\.?\d{3}\.?\d{1}-?\d{3}\.?\d{3}\b/', '[REDACTED_NPWP]', $redacted);
        $redacted = preg_replace('/\b(?:passport|paspor)\s*(?:no|number|nomor)?\s*[:=]?\s*[A-Z0-9]{6,10}\b/iu', '[REDACTED_PASSPORT]', $redacted);

        // 5. Redact Candidate Name / Known PII if provided
        if (is_array($candidateName)) {
            foreach ($candidateName as $item) {
                if ($item && is_string($item) && trim($item) !== '') {
                    $val = trim($item);
                    $redacted = preg_replace('/' . preg_quote($val, '/') . '/iu', "[{$code}_NAME]", $redacted);

                    // Redact distinct words of name if length >= 3
                    $parts = preg_split('/\s+/', $val);
                    foreach ($parts as $part) {
                        if (mb_strlen($part) >= 3 && !in_array(strtolower($part), ['van', 'bin', 'binti', 'dan', 'and', 'the'], true)) {
                            $redacted = preg_replace('/\b' . preg_quote($part, '/') . '\b/iu', "[{$code}_NAME]", $redacted);
                        }
                    }
                }
            }
        } elseif ($candidateName && is_string($candidateName) && trim($candidateName) !== '') {
            $name = trim($candidateName);
            $redacted = preg_replace('/' . preg_quote($name, '/') . '/iu', "[{$code}_NAME]", $redacted);

            $parts = preg_split('/\s+/', $name);
            foreach ($parts as $part) {
                if (mb_strlen($part) >= 3 && !in_array(strtolower($part), ['van', 'bin', 'binti', 'dan', 'and', 'the'], true)) {
                    $redacted = preg_replace('/\b' . preg_quote($part, '/') . '\b/iu', "[{$code}_NAME]", $redacted);
                }
            }
        }

        // 6. Redact explicit Date of Birth / Age patterns
        $redacted = preg_replace(
            '/\b(?:date of birth|dob|tanggal lahir|tgl lahir|tpt\/tgl lahir|tempat\/tgl lahir)\s*[:=]?\s*[^\n,;]+/iu',
            '[REDACTED_DOB]',
            $redacted
        );
        $redacted = preg_replace(
            '/\b(?:age|usia|umur)\s*[:=]?\s*\d{1,2}\s*(?:years?|thn?|tahun)?\b/iu',
            '[REDACTED_AGE]',
            $redacted
        );

        // 7. Redact Religion
        $redacted = preg_replace(
            '/\b(?:religion|agama)\s*[:=]?\s*(?:islam|kristen|katolik|protestan|hindu|buddha|konghucu|christian|catholic|muslim|jewish)\b/iu',
            '[REDACTED_RELIGION]',
            $redacted
        );

        // 8. Redact Marital Status
        $redacted = preg_replace(
            '/\b(?:marital status|status pernikahan|status perkawinan)\s*[:=]?\s*(?:single|married|lajang|menikah|belum menikah|cerai|divorced|widowed)\b/iu',
            '[REDACTED_MARITAL_STATUS]',
            $redacted
        );

        // 9. Redact Gender
        $redacted = preg_replace(
            '/\b(?:gender|jenis kelamin|sex)\s*[:=]?\s*(?:male|female|pria|wanita|laki-laki|perempuan)\b/iu',
            '[REDACTED_GENDER]',
            $redacted
        );

        // 10. Redact full postal address lines if labeled
        $redacted = preg_replace(
            '/\b(?:address|alamat|domisili|tempat tinggal)\s*[:=]?\s*[^\n]{10,100}/iu',
            '[REDACTED_ADDRESS]',
            $redacted
        );

        // 11. Neutralize Prompt Injections
        $injectionPatterns = [
            '/(?:ignore|disregard|forget)\s+(?:previous|all|the|above)\s+(?:instructions|prompts|rules|commands)[^\n.]*/iu',
            '/(?:system\s+override|system\s+prompt|developer\s+mode|jailbreak|dan\s+mode)[^\n.]*/iu',
            '/(?:you\s+must|always|force)\s+(?:award|give|set|assign)\s+(?:a\s+score|score\s+of|full\s+score|100)[^\n.]*/iu',
        ];

        foreach ($injectionPatterns as $pattern) {
            $redacted = preg_replace($pattern, '[SUSPICIOUS_PROMPT_INJECTION_NEUTRALIZED]', $redacted);
        }

        // Enclose in delimiter tags with candidate code header
        return "Candidate Reference: {$code}\n"
            . "<untrusted_cv_text>\n"
            . trim($redacted) . "\n"
            . "</untrusted_cv_text>";
    }
}
