<?php

namespace App\Services;

class ExpressRateExcelParserService
{
    private const REQUIRED_HEADERS = [
        'SHIPMENTTYPE',
        'ZONE',
        'WEIGHT',
        'PRICE',
    ];

    private const MAX_ROWS = 20000;

    public function parse(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException(
                'File Excel tidak ditemukan.'
            );
        }

        $matrix = class_exists(
            '\\PhpOffice\\PhpSpreadsheet\\IOFactory'
        )
            ? $this->readWithPhpSpreadsheet($filePath)
            : $this->readNativeXlsx($filePath);

        $parsed = $this->parseNormalized($matrix);

        if ($parsed === null) {
            $parsed = $this->parseMatrix($matrix);
        }

        if ($parsed === null) {
            throw new \RuntimeException(
                'Format Excel tidak dikenali. Gunakan sheet IMPORT_READY ' .
                'atau format matriks WEIGHT / Zone 1–8.'
            );
        }

        return $this->validateAndSummarize(
            $parsed['rows'],
            $parsed['format'],
            $parsed['sheet_name']
        );
    }

    private function readWithPhpSpreadsheet(
        string $filePath
    ): array {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
            $filePath
        );

        $worksheet = $spreadsheet->getSheetByName(
            'IMPORT_READY'
        ) ?? $spreadsheet->getActiveSheet();

        return [
            'sheet_name' => $worksheet->getTitle(),
            'rows'       => $worksheet->toArray(
                null,
                true,
                false,
                false
            ),
        ];
    }

    private function readNativeXlsx(string $filePath): array
    {
        if (
            !class_exists('ZipArchive')
            || !class_exists('DOMDocument')
        ) {
            throw new \RuntimeException(
                'Server belum memiliki pembaca XLSX. Aktifkan extension ' .
                'php_zip dan php_xml, atau install phpoffice/phpspreadsheet.'
            );
        }

        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException(
                'File bukan XLSX yang valid atau file rusak.'
            );
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetInfo     = $this->resolveWorksheet($zip);
            $sheetXml      = $zip->getFromName(
                $sheetInfo['path']
            );

            if ($sheetXml === false) {
                throw new \RuntimeException(
                    'Worksheet Excel tidak dapat dibaca.'
                );
            }

            return [
                'sheet_name' => $sheetInfo['name'],
                'rows'       => $this->worksheetToRows(
                    $sheetXml,
                    $sharedStrings
                ),
            ];
        } finally {
            $zip->close();
        }
    }

    private function resolveWorksheet(\ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName(
            'xl/workbook.xml'
        );
        $relsXml = $zip->getFromName(
            'xl/_rels/workbook.xml.rels'
        );

        if ($workbookXml === false || $relsXml === false) {
            throw new \RuntimeException(
                'Struktur workbook XLSX tidak lengkap.'
            );
        }

        $workbook = $this->loadDom($workbookXml);
        $rels     = $this->loadDom($relsXml);

        $relationships = [];

        foreach (
            $rels->getElementsByTagNameNS('*', 'Relationship')
            as $relationship
        ) {
            $relationships[
                $relationship->getAttribute('Id')
            ] = $relationship->getAttribute('Target');
        }

        $sheets = [];

        foreach (
            $workbook->getElementsByTagNameNS('*', 'sheet')
            as $sheet
        ) {
            $relationshipId = $sheet->getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                'id'
            );

            if ($relationshipId === '') {
                $relationshipId = $sheet->getAttribute('r:id');
            }

            $target = $relationships[$relationshipId] ?? null;

            if ($target === null) {
                continue;
            }

            $sheets[] = [
                'name' => $sheet->getAttribute('name'),
                'path' => $this->normalizeSheetPath($target),
            ];
        }

        if ($sheets === []) {
            throw new \RuntimeException(
                'Workbook tidak memiliki worksheet.'
            );
        }

        foreach ($sheets as $sheet) {
            if (
                strtoupper(trim($sheet['name']))
                === 'IMPORT_READY'
            ) {
                return $sheet;
            }
        }

        return $sheets[0];
    }

    private function normalizeSheetPath(string $target): string
    {
        $target = str_replace('\\\\', '/', $target);
        $target = ltrim($target, '/');

        if (str_starts_with($target, 'xl/')) {
            return $target;
        }

        while (str_starts_with($target, '../')) {
            $target = substr($target, 3);
        }

        return 'xl/' . $target;
    }

    private function readSharedStrings(
        \ZipArchive $zip
    ): array {
        $xml = $zip->getFromName(
            'xl/sharedStrings.xml'
        );

        if ($xml === false) {
            return [];
        }

        $dom     = $this->loadDom($xml);
        $strings = [];

        foreach (
            $dom->getElementsByTagNameNS('*', 'si')
            as $item
        ) {
            $text = '';

            foreach (
                $item->getElementsByTagNameNS('*', 't')
                as $textNode
            ) {
                $text .= $textNode->textContent;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    private function worksheetToRows(
        string $xml,
        array $sharedStrings
    ): array {
        $dom  = $this->loadDom($xml);
        $data = [];
        $maxRow = 0;
        $maxCol = 0;

        foreach (
            $dom->getElementsByTagNameNS('*', 'row')
            as $rowNode
        ) {
            $rowNumber = (int) $rowNode->getAttribute('r');

            if ($rowNumber <= 0) {
                $rowNumber = $maxRow + 1;
            }

            $maxRow = max($maxRow, $rowNumber);

            foreach (
                $rowNode->getElementsByTagNameNS('*', 'c')
                as $cell
            ) {
                $reference = $cell->getAttribute('r');
                $column = $this->columnIndexFromReference(
                    $reference
                );

                $maxCol = max($maxCol, $column);
                $data[$rowNumber - 1][$column] =
                    $this->readCellValue(
                        $cell,
                        $sharedStrings
                    );
            }
        }

        $rows = [];

        for ($row = 0; $row < $maxRow; $row++) {
            $values = [];

            for ($col = 0; $col <= $maxCol; $col++) {
                $values[] = $data[$row][$col] ?? null;
            }

            $rows[] = $values;
        }

        return $rows;
    }

    private function readCellValue(
        \DOMElement $cell,
        array $sharedStrings
    ) {
        $type = $cell->getAttribute('t');

        if ($type === 'inlineStr') {
            $text = '';

            foreach (
                $cell->getElementsByTagNameNS('*', 't')
                as $node
            ) {
                $text .= $node->textContent;
            }

            return $text;
        }

        $valueNodes = $cell->getElementsByTagNameNS('*', 'v');
        $raw = $valueNodes->length > 0
            ? $valueNodes->item(0)->textContent
            : '';

        if ($type === 's') {
            return $sharedStrings[(int) $raw] ?? '';
        }

        if ($type === 'b') {
            return $raw === '1';
        }

        if ($type === 'str' || $type === 'e') {
            return $raw;
        }

        if ($raw === '') {
            return null;
        }

        return is_numeric($raw)
            ? (float) $raw
            : $raw;
    }

    private function columnIndexFromReference(
        string $reference
    ): int {
        if (!preg_match('/^([A-Z]+)/i', $reference, $match)) {
            return 0;
        }

        $letters = strtoupper($match[1]);
        $index   = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26)
                + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function loadDom(string $xml): \DOMDocument
    {
        $dom = new \DOMDocument();

        $loaded = $dom->loadXML(
            $xml,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        if (!$loaded) {
            throw new \RuntimeException(
                'XML di dalam file XLSX tidak valid.'
            );
        }

        return $dom;
    }

    private function parseNormalized(array $matrix): ?array
    {
        $rows      = $matrix['rows'];
        $headerRow = null;
        $columns   = [];

        foreach (array_slice($rows, 0, 30, true) as $index => $row) {
            $found = [];

            foreach ($row as $column => $value) {
                $token = $this->normalizeToken($value);

                if (in_array($token, self::REQUIRED_HEADERS, true)) {
                    $found[$token] = $column;
                }
            }

            if (count($found) === count(self::REQUIRED_HEADERS)) {
                $headerRow = $index;
                $columns   = $found;
                break;
            }
        }

        if ($headerRow === null) {
            return null;
        }

        $parsed = [];

        for ($index = $headerRow + 1; $index < count($rows); $index++) {
            $row = $rows[$index];

            $type = $row[$columns['SHIPMENTTYPE']] ?? null;
            $zone = $row[$columns['ZONE']] ?? null;
            $weight = $row[$columns['WEIGHT']] ?? null;
            $price = $row[$columns['PRICE']] ?? null;

            if (
                $this->isBlank($type)
                && $this->isBlank($zone)
                && $this->isBlank($weight)
                && $this->isBlank($price)
            ) {
                continue;
            }

            $parsed[] = [
                'shipment_type' => $type,
                'zone'          => $zone,
                'weight'        => $weight,
                'price'         => $price,
                'source_row'    => $index + 1,
            ];
        }

        return [
            'format'     => 'NORMALIZED',
            'sheet_name' => $matrix['sheet_name'],
            'rows'       => $parsed,
        ];
    }

    private function parseMatrix(array $matrix): ?array
    {
        $rows    = $matrix['rows'];
        $zoneMap = [];

        foreach (array_slice($rows, 0, 20, true) as $row) {
            foreach ($row as $column => $value) {
                if (
                    is_string($value)
                    && preg_match(
                        '/^\\s*zone\\s*(\\d+)\\s*$/i',
                        $value,
                        $match
                    )
                ) {
                    $zoneMap[$column] = (int) $match[1];
                }
            }
        }

        if ($zoneMap === []) {
            return null;
        }

        $currentType = null;
        $parsed      = [];

        foreach ($rows as $index => $row) {
            $first = $row[0] ?? null;
            $token = $this->normalizeToken($first);

            if (in_array($token, ['DOCUMENT', 'PACKAGE'], true)) {
                $currentType = $token;
                continue;
            }

            if ($currentType === null || !$this->isNumericValue($first)) {
                continue;
            }

            foreach ($zoneMap as $column => $zone) {
                $price = $row[$column] ?? null;

                if ($this->isBlank($price)) {
                    continue;
                }

                $parsed[] = [
                    'shipment_type' => $currentType,
                    'zone'          => $zone,
                    'weight'        => $first,
                    'price'         => $price,
                    'source_row'    => $index + 1,
                ];
            }
        }

        if ($parsed === []) {
            return null;
        }

        return [
            'format'     => 'MATRIX',
            'sheet_name' => $matrix['sheet_name'],
            'rows'       => $parsed,
        ];
    }

    private function validateAndSummarize(
        array $rows,
        string $format,
        string $sheetName
    ): array {
        if (count($rows) > self::MAX_ROWS) {
            throw new \RuntimeException(
                'Jumlah baris melebihi batas ' . self::MAX_ROWS . '.'
            );
        }

        $validRows = [];
        $errors    = [];
        $warnings  = [];
        $seen      = [];

        foreach ($rows as $row) {
            $sourceRow = (int) ($row['source_row'] ?? 0);
            $type = $this->normalizeToken(
                $row['shipment_type'] ?? null
            );
            $zone   = $this->numericValue($row['zone'] ?? null);
            $weight = $this->numericValue($row['weight'] ?? null);
            $price  = $this->numericValue(
                $row['price'] ?? null,
                true
            );

            if (!in_array($type, ['DOCUMENT', 'PACKAGE'], true)) {
                $errors[] = $this->rowError(
                    $sourceRow,
                    'Shipment Type harus DOCUMENT atau PACKAGE.'
                );
                continue;
            }

            if ($zone === null || floor($zone) !== $zone || $zone < 1 || $zone > 99) {
                $errors[] = $this->rowError(
                    $sourceRow,
                    'Zone harus bilangan bulat 1–99.'
                );
                continue;
            }

            if ($weight === null || $weight <= 0) {
                $errors[] = $this->rowError(
                    $sourceRow,
                    'Weight harus lebih besar dari 0.'
                );
                continue;
            }

            if ($price === null || $price < 0) {
                $errors[] = $this->rowError(
                    $sourceRow,
                    'Price harus angka dan tidak boleh negatif.'
                );
                continue;
            }

            $key = sprintf(
                '%s|%d|%.2f',
                $type,
                (int) $zone,
                round($weight, 2)
            );

            if (isset($seen[$key])) {
                $errors[] = $this->rowError(
                    $sourceRow,
                    'Duplikat Type + Zone + Weight dengan baris ' .
                    $seen[$key] . '.'
                );
                continue;
            }

            $seen[$key] = $sourceRow;

            if ($type === 'DOCUMENT' && $weight > 2) {
                $warnings[] = $this->rowError(
                    $sourceRow,
                    'DOCUMENT lebih dari 2 Kg. Pastikan ini memang tarif vendor.'
                );
            }

            $validRows[] = [
                'shipment_type' => $type,
                'zone'          => (int) $zone,
                'weight'        => round($weight, 2),
                'price'         => round($price, 2),
                'source_row'    => $sourceRow,
            ];
        }

        if ($errors !== []) {
            throw new \RuntimeException(
                'Excel memiliki ' . count($errors) . ' kesalahan. ' .
                implode(' | ', array_slice($errors, 0, 12))
            );
        }

        if ($validRows === []) {
            throw new \RuntimeException(
                'Tidak ada tarif valid yang dapat diimpor.'
            );
        }

        $zones = array_values(
            array_unique(
                array_column($validRows, 'zone')
            )
        );
        sort($zones);

        $documentRows = array_values(
            array_filter(
                $validRows,
                static fn (array $row): bool =>
                    $row['shipment_type'] === 'DOCUMENT'
            )
        );

        $packageRows = array_values(
            array_filter(
                $validRows,
                static fn (array $row): bool =>
                    $row['shipment_type'] === 'PACKAGE'
            )
        );

        return [
            'format'     => $format,
            'sheet_name' => $sheetName,
            'rows'       => $validRows,
            'warnings'   => array_values(array_unique($warnings)),
            'summary'    => [
                'total_rows'       => count($validRows),
                'document_rows'    => count($documentRows),
                'package_rows'     => count($packageRows),
                'zones'            => $zones,
                'minimum_weight'   => min(array_column($validRows, 'weight')),
                'maximum_weight'   => max(array_column($validRows, 'weight')),
                'minimum_price'    => min(array_column($validRows, 'price')),
                'maximum_price'    => max(array_column($validRows, 'price')),
            ],
        ];
    }

    private function normalizeToken($value): string
    {
        return preg_replace(
            '/[^A-Z0-9]+/',
            '',
            strtoupper(trim((string) $value))
        ) ?? '';
    }

    private function isBlank($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function isNumericValue($value): bool
    {
        return $this->numericValue($value) !== null;
    }

    private function numericValue(
        $value,
        bool $preferThousands = false
    ): ?float {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $negativeByParentheses = false;

        if (
            str_starts_with($text, '(')
            && str_ends_with($text, ')')
        ) {
            $negativeByParentheses = true;
        }

        $text = str_replace(
            [
                "\u{00A0}",
                "\u{202F}",
                ' ',
            ],
            '',
            $text
        );

        $text = preg_replace(
            '/[^0-9,.\-]/u',
            '',
            $text
        ) ?? '';

        if (
            $text === ''
            || $text === '-'
            || $text === '.'
            || $text === ','
        ) {
            return null;
        }

        /*
         * IDR price cells can arrive from PhpSpreadsheet as
         * formatted strings such as:
         *
         * 384.780
         * 1.044.780
         * 19.479.350
         *
         * Those dots are thousands separators, not decimals.
         */
        if ($preferThousands) {
            if (
                preg_match(
                    '/^-?\d{1,3}(?:\.\d{3})+(?:,\d+)?$/',
                    $text
                )
            ) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } elseif (
                preg_match(
                    '/^-?\d{1,3}(?:,\d{3})+(?:\.\d+)?$/',
                    $text
                )
            ) {
                $text = str_replace(',', '', $text);
            }
        }

        $lastComma = strrpos($text, ',');
        $lastDot   = strrpos($text, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($lastComma !== false) {
            $text = str_replace(',', '.', $text);
        }

        if (!is_numeric($text)) {
            return null;
        }

        $number = (float) $text;

        return $negativeByParentheses
            ? -abs($number)
            : $number;
    }

    private function rowError(int $row, string $message): string
    {
        return ($row > 0 ? 'Baris ' . $row . ': ' : '') . $message;
    }
}
