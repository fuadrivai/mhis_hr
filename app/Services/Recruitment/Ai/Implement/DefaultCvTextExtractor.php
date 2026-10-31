<?php

namespace App\Services\Recruitment\Ai\Implement;

use App\Services\Recruitment\Ai\AiMatchingProvider;
use App\Services\Recruitment\Ai\CvTextExtractor;
use App\Services\Recruitment\Ai\PdfRasterizer;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Smalot\PdfParser\Config as PdfParserConfig;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;
use ZipArchive;

class DefaultCvTextExtractor implements CvTextExtractor
{
    public function __construct(
        protected ?AiMatchingProvider $aiProvider = null,
        protected ?PdfRasterizer $rasterizer = null
    ) {
        $this->rasterizer = $rasterizer ?? new PdfRasterizer();
    }

    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("CV file does not exist or is not readable: {$filePath}");
        }

        // Ensure sufficient memory limit for processing document streams
        if ((int) ini_get('memory_limit') < 256) {
            @ini_set('memory_limit', '256M');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $text = match ($extension) {
            'pdf' => $this->extractPdf($filePath),
            'docx' => $this->extractDocx($filePath),
            'doc' => $this->extractDoc($filePath),
            'txt' => (string) file_get_contents($filePath),
            'png', 'jpg', 'jpeg', 'webp' => $this->extractVisual($filePath),
            default => $this->detectAndExtract($filePath),
        };

        // Normalize whitespaces and clean control characters
        $clean = preg_replace('/[^\P{C}\n\r\t]+/u', '', $text);
        $clean = preg_replace('/[ \t]+/', ' ', $clean);
        $clean = preg_replace('/\n\s*\n+/', "\n\n", $clean);

        return trim($clean);
    }

    private function extractPdf(string $filePath): string
    {
        $parsedText = '';
        try {
            // Configure Smalot parser to not retain heavy raw image content in RAM
            $config = new PdfParserConfig();
            $config->setRetainImageContent(false);
            $parser = new PdfParser([], $config);

            $pdf = $parser->parseFile($filePath);
            $parsedText = (string) $pdf->getText();
        } catch (Throwable $e) {
            Log::warning("PdfParser failed for {$filePath}: " . $e->getMessage());
        }

        // If PDF contains sufficient selectable text (>= 50 chars), return immediately
        if (mb_strlen(trim($parsedText)) >= 50) {
            return $parsedText;
        }

        // Scanned or image-based PDF detected:
        // When AI Vision OCR is available and configured (non-mock), rasterize pages to PNG and run OCR
        if ($this->aiProvider && config('ai.provider') !== 'mock' && !empty(config('ai.openai.api_key'))) {
            try {
                $renderedPages = $this->rasterizer?->rasterize($filePath, 2) ?? [];
                $ocrChunks = [];

                foreach ($renderedPages as $pagePng) {
                    $ocr = $this->aiProvider->extractTextFromVisualDocument($pagePng);
                    if (!empty(trim($ocr))) {
                        $ocrChunks[] = $ocr;
                    }
                    @unlink($pagePng);
                }

                if (!empty($ocrChunks)) {
                    $fullOcr = implode("\n\n", $ocrChunks);
                    if (mb_strlen(trim($fullOcr)) >= 50) {
                        return $fullOcr;
                    }
                }
            } catch (Throwable $e) {
                Log::warning("AI Vision OCR failed on scanned PDF {$filePath}: " . $e->getMessage());
            }
        }

        return $parsedText;
    }

    private function extractVisual(string $filePath): string
    {
        if ($this->aiProvider) {
            return $this->aiProvider->extractTextFromVisualDocument($filePath);
        }

        return '';
    }

    private function extractDocx(string $filePath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Failed to open DOCX archive.");
        }

        $xmlIndex = $zip->locateName('word/document.xml');
        if ($xmlIndex === false) {
            $zip->close();
            throw new RuntimeException("Invalid DOCX format: word/document.xml missing.");
        }

        $xml = $zip->getFromIndex($xmlIndex);
        $zip->close();

        if (!$xml) {
            return '';
        }

        // Preserve paragraph and table line breaks
        $xml = preg_replace('/<\/(w:p|w:tr)>/', "\n", $xml);
        $xml = preg_replace('/<w:tab\/>/', "\t", $xml);
        $xml = preg_replace('/<w:br\/>/', "\n", $xml);

        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractDoc(string $filePath): string
    {
        $content = (string) file_get_contents($filePath);
        if (empty($content)) {
            return '';
        }

        preg_match_all('/[\x20-\x7E\xA0-\xFF\r\n\t]{4,}/', $content, $matches);
        return implode("\n", $matches[0] ?? []);
    }

    private function detectAndExtract(string $filePath): string
    {
        $mime = mime_content_type($filePath);

        if ($mime === 'application/pdf') {
            return $this->extractPdf($filePath);
        }

        if ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return $this->extractDocx($filePath);
        }

        if ($mime === 'application/msword') {
            return $this->extractDoc($filePath);
        }

        if (str_starts_with((string) $mime, 'image/')) {
            return $this->extractVisual($filePath);
        }

        if (str_starts_with((string) $mime, 'text/')) {
            return (string) file_get_contents($filePath);
        }

        throw new RuntimeException("Unsupported file type for text extraction: {$mime}");
    }
}
