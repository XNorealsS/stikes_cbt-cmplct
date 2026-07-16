<?php

namespace App\Services;

use App\Models\Question;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use SimpleXMLElement;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuestionImportService
{
    /**
     * Import questions from an Excel (.xlsx or .xls) file using PhpSpreadsheet
     */
    public function importExcel(string $filePath, int $courseId, ?string $category = null, ?int $bankSoalId = null): array
    {
        $importedCount = 0;
        $errors = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            if (count($rows) <= 1) {
                return ['success' => false, 'count' => 0, 'errors' => ['File Excel kosong atau hanya berisi header.']];
            }

            // Detect new format with "Tipe Soal" column (index 2) by checking if the header row has at least 10 elements
            $isNewFormat = (count($rows[0]) >= 10);

            DB::beginTransaction();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Skip if question text is empty
                if (empty($row[0])) {
                    continue;
                }

                $questionText = trim($row[0]);
                $difficulty = strtolower(trim($row[1] ?? 'sedang'));
                if (!in_array($difficulty, ['mudah', 'sedang', 'sulit'])) {
                    $difficulty = 'sedang';
                }

                if ($isNewFormat) {
                    $type = strtolower(trim($row[2] ?? 'pg'));
                    if (!in_array($type, ['pg', 'essai'])) {
                        $type = 'pg';
                    }

                    if ($type === 'essai') {
                        $optionA = '-';
                        $optionB = '-';
                        $optionC = '-';
                        $optionD = '-';
                        $optionE = '-';
                        $correctOption = isset($row[8]) ? trim($row[8]) : '';
                        $explanation = isset($row[9]) ? trim($row[9]) : null;
                    } else {
                        // Skip PG rows that don't have option A-D filled
                        if (empty($row[3]) || empty($row[4]) || empty($row[5]) || empty($row[6])) {
                            continue;
                        }
                        $optionA = trim($row[3]);
                        $optionB = trim($row[4]);
                        $optionC = trim($row[5]);
                        $optionD = trim($row[6]);
                        $optionE = trim($row[7] ?? '-');
                        $correctOption = strtoupper(trim($row[8] ?? 'A'));
                        if (!in_array($correctOption, ['A', 'B', 'C', 'D', 'E'])) {
                            $correctOption = 'A';
                        }
                        $explanation = isset($row[9]) ? trim($row[9]) : null;
                    }
                } else {
                    // Old format: always PG (9 columns)
                    // Skip PG rows that don't have option A-D filled
                    if (empty($row[2]) || empty($row[3]) || empty($row[4]) || empty($row[5])) {
                        continue;
                    }
                    $type = 'pg';
                    $optionA = trim($row[2]);
                    $optionB = trim($row[3]);
                    $optionC = trim($row[4]);
                    $optionD = trim($row[5]);
                    $optionE = trim($row[6] ?? '-');
                    $correctOption = strtoupper(trim($row[7] ?? 'A'));
                    if (!in_array($correctOption, ['A', 'B', 'C', 'D', 'E'])) {
                        $correctOption = 'A';
                    }
                    $explanation = isset($row[8]) ? trim($row[8]) : null;
                }

                Question::create([
                    'course_id' => $courseId,
                    'bank_soal_id' => $bankSoalId,
                    'category' => $category ?? 'Excel Import',
                    'difficulty' => $difficulty,
                    'question_type' => $type,
                    'question_text' => $questionText,
                    'option_a' => $optionA,
                    'option_b' => $optionB,
                    'option_c' => $optionC,
                    'option_d' => $optionD,
                    'option_e' => $optionE,
                    'correct_option' => $correctOption,
                    'explanation' => $explanation,
                ]);

                $importedCount++;
            }

            DB::commit();
            ActivityLog::log('Import Bank Soal (Excel)', "Berhasil mengimpor $importedCount soal.");
            return ['success' => true, 'count' => $importedCount, 'errors' => []];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'count' => 0, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Import questions from a Word (.docx) table template
     */
    public function importWord(string $filePath, int $courseId, ?string $category = null, ?int $bankSoalId = null): array
    {
        $importedCount = 0;
        $errors = [];

        try {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                return ['success' => false, 'count' => 0, 'errors' => ['Gagal membuka berkas Word (.docx). Pastikan formatnya sesuai.']];
            }

            $xmlContent = '';
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlContent = $zip->getFromIndex($index);
            }
            $zip->close();

            if (empty($xmlContent)) {
                return ['success' => false, 'count' => 0, 'errors' => ['Berkas Word rusak atau tidak memiliki data XML document.']];
            }

            $xml = new SimpleXMLElement($xmlContent);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            // Find all tables
            $tables = $xml->xpath('//w:tbl');
            if (empty($tables)) {
                return ['success' => false, 'count' => 0, 'errors' => ['Tidak ditemukan tabel soal di dalam berkas Word.']];
            }

            DB::beginTransaction();

            // We assume the first table is the question bank table
            $rows = $tables[0]->xpath('.//w:tr');
            
            // Skip the header row
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $cells = $row->xpath('.//w:tc');

                if (count($cells) < 8) {
                    continue; // Row doesn't have enough cells
                }

                // Extract cell text values
                $questionText = $this->getCellText($cells[1]);
                if (empty($questionText)) {
                    continue; // Skip empty question texts
                }

                $optionA = $this->getCellText($cells[2]);
                $optionB = $this->getCellText($cells[3]);
                $optionC = $this->getCellText($cells[4]);
                $optionD = $this->getCellText($cells[5]);
                $optionE = $this->getCellText($cells[6]);

                $correctOption = strtoupper(trim($this->getCellText($cells[7])));
                if (!in_array($correctOption, ['A', 'B', 'C', 'D', 'E'])) {
                    $correctOption = 'A';
                }

                $difficulty = 'sedang';
                if (isset($cells[8])) {
                    $difficulty = strtolower(trim($this->getCellText($cells[8])));
                    if (!in_array($difficulty, ['mudah', 'sedang', 'sulit'])) {
                        $difficulty = 'sedang';
                    }
                }

                Question::create([
                    'course_id' => $courseId,
                    'bank_soal_id' => $bankSoalId,
                    'category' => $category ?? 'Word Import',
                    'difficulty' => $difficulty,
                    'question_text' => $questionText,
                    'option_a' => $optionA,
                    'option_b' => $optionB,
                    'option_c' => $optionC,
                    'option_d' => $optionD,
                    'option_e' => $optionE,
                    'correct_option' => $correctOption,
                ]);

                $importedCount++;
            }

            DB::commit();
            ActivityLog::log('Import Bank Soal (Word)', "Berhasil mengimpor $importedCount soal.");
            return ['success' => true, 'count' => $importedCount, 'errors' => []];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'count' => 0, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Helper to retrieve all text runs within a table cell w:tc
     */
    private function getCellText(SimpleXMLElement $cellElement): string
    {
        $text = '';
        $cellElement->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paragraphs = $cellElement->xpath('.//w:p');

        foreach ($paragraphs as $p) {
            $p->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $runs = $p->xpath('.//w:r');
            $pText = '';
            foreach ($runs as $r) {
                $r->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $texts = $r->xpath('.//w:t');
                foreach ($texts as $t) {
                    $pText .= (string)$t;
                }
            }
            $text .= ($text === '' ? '' : "\n") . trim($pText);
        }

        return trim($text);
    }
}
