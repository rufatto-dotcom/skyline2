<?php

class PdfExporter
{
    public function export(string $modulo, array $rows): void
    {
        require_once CORE_SERVICES . '/export/pdf/PdfRenderer.php';

        $renderer = new PdfRenderer($modulo);
        $renderer->render($rows);
    }
}
