<?php

class CsvExporter {

    public function export(string $filename, array $rows, array $options = []):void {
        if (empty($rows)) {
            throw new RuntimeException("Nada para exportar");
        }

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, array_keys($rows[0]), ';');

        foreach ($rows as $row) {
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }
}

?>