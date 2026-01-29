<?php

const ROUTER_TEMPLATE = <<<'PHP'
<?php
$action = $_REQUEST['action'] ?? 'Index';
$modulo = $_REQUEST['modulo'];

$id = $_REQUEST['id'] ?? null;
$bean = $dao->select($modulo, $id);
$rows = $dao->selectAll($modulo);

$metadataModule = $metadata->__get($modulo);

$fields = $metadataModule['fields'];
uasort($fields, function ($a, $b) {
    return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
});
$moduleLabel = $metadataModule['label'];

switch ($action) {
    case 'EditView':
        include __DIR__ . '/views/edit.php';
        break;
    case 'DetailView':
        include __DIR__ . '/views/detail.php';
        break;
    case 'Export':
        $format = $_POST['format'] ?? 'csv';
        $id = $_POST['id'] ?? null;

        require_once CORE_SERVICES . '/export/getExporter.php';

        if ($id) {
            $row = $dao->select($modulo, $id);
            $rows = $row ? [$row] : [];
        } else {
            $rows = $dao->selectAll($modulo);
        }

        $exporter = getExporter($format);
        $exporter->export($modulo, $rows);
        break;

    default:
        include __DIR__ . '/views/index.php';
        break;
}
PHP;

const INDEX_TEMPLATE = <<<'PHP'
<?php
renderComponent('table', [
    'moduleLabel' => $moduleLabel,
    'rows' => $rows,
    'fields' => $fields,
    'modulo' => $modulo,
]);
?>
PHP;

const EDIT_AND_VIEW_TEMPLATE =  <<<'PHP'
<?php
include_once CORE_LAYOUT . '/actionBtn.php';
renderComponent('form', [
    'moduleLabel' => $moduleLabel,
    'modulo' => $modulo,
    'beanId' => $id,
    'bean' => $bean,
    'metadata' => $metadataModule,
    'fields' => $fields,
]);
PHP;

const PDF_TEMPLATE = <<<'PHP'
<?php
extract($data);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $moduleLabel, 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
foreach ($rows as $row) {
    foreach ($fields as $field => $meta) {
        $label = $meta['label'] ?? $field;
        $value = $row[$field] ?? '';
        $pdf->Cell(50, 8, $label, 1);
        $pdf->Cell(0, 8, $value, 1, 1);
    }
    $pdf->Ln(4);
}
PHP;


?>