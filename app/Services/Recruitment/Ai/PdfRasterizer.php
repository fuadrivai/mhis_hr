<?php

namespace App\Services\Recruitment\Ai;

use Illuminate\Support\Facades\Log;
use Throwable;

class PdfRasterizer
{
    /**
     * Render the first N pages of a PDF to temporary PNG files.
     *
     * @param string $pdfPath
     * @param int $maxPages
     * @return array<string> Array of paths to temporary PNG files.
     */
    public function rasterize(string $pdfPath, int $maxPages = 2): array
    {
        if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            return [];
        }

        $tmpDir = sys_get_temp_dir();
        $prefix = 'mhis_pdf_page_' . uniqid() . '_';

        // Python script that uses PyMuPDF (fitz) or fallback to render PDF pages to PNG
        $pythonScript = <<<'PY'
import sys, os, warnings
warnings.filterwarnings("ignore")
os.environ["PYMUPDF_DEPRECATION_WARNING"] = "0"
try:
    import fitz
    pdf_path = sys.argv[1]
    tmp_dir = sys.argv[2]
    prefix = sys.argv[3]
    max_pages = int(sys.argv[4])

    doc = fitz.open(pdf_path)
    count = min(len(doc), max_pages)
    out_files = []
    for i in range(count):
        page = doc[i]
        pix = page.get_pixmap(dpi=130)
        out_file = os.path.join(tmp_dir, f"{prefix}{i}.png")
        pix.save(out_file)
        out_files.append(out_file)
    print(";".join(out_files))
except Exception as e:
    sys.stderr.write(str(e))
    sys.exit(1)
PY;

        $scriptPath = $tmpDir . '/render_pdf_' . uniqid() . '.py';
        file_put_contents($scriptPath, $pythonScript);

        $cmd = 'python ' . escapeshellarg($scriptPath) . ' '
            . escapeshellarg(realpath($pdfPath) ?: $pdfPath) . ' '
            . escapeshellarg($tmpDir) . ' '
            . escapeshellarg($prefix) . ' '
            . $maxPages;

        $output = [];
        $exitCode = 0;

        try {
            exec($cmd, $output, $exitCode);
        } catch (Throwable $e) {
            Log::warning("PdfRasterizer execution failed: " . $e->getMessage());
        } finally {
            @unlink($scriptPath);
        }

        if ($exitCode !== 0 || empty($output)) {
            Log::info("PdfRasterizer: python render returned code {$exitCode}, output: " . implode(' | ', $output));
            return [];
        }

        // PyMuPDF may print a deprecation warning before the actual output.
        // Find the last non-empty line that contains file paths (semicolon-separated).
        $dataLine = '';
        foreach (array_reverse($output) as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && !str_starts_with(strtolower($trimmed), 'warning:') && !str_starts_with(strtolower($trimmed), 'deprecat')) {
                $dataLine = $trimmed;
                break;
            }
        }

        if (empty($dataLine)) {
            return [];
        }

        $renderedFiles = explode(';', $dataLine);
        return array_filter($renderedFiles, fn ($f) => file_exists($f) && filesize($f) > 100);
    }
}

