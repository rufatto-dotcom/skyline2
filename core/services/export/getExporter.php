<?php

require_once __DIR__ . '/CsvExporter.php';
require_once __DIR__ . '/PdfExporter.php';
require_once __DIR__ . '/XlsxExporter.php';

function getExporter(string $format)
{
    return match (strtolower($format)) {
        'csv' => new CsvExporter(),
        'pdf' => new PdfExporter(),
        'xlsx' => new XlsxExporter(),
        default => throw new InvalidArgumentException('Formato inválido'),
    };
}
