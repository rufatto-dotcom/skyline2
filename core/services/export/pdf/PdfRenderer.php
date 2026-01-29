<?php
class PdfRenderer
{
    protected string $modulo;

    public function __construct(string $modulo)
    {
        $this->modulo = $modulo;
        require_once CORE_SERVICES . '/export/pdf/fpdf/fpdf.php';
    }

    public function render(array $rows): void
    {
        $template = $this->resolveTemplate();
        $data = $this->getTemplateData($rows);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);

        // template escreve direto no PDF
        require $template;

        $pdf->Output('D', $this->modulo . '.pdf');
        exit;
    }

    protected function resolveTemplate(): string
    {
        $custom = MODULES_PATH . "/{$this->modulo}/pdf/template.php";
        if (file_exists($custom)) {
            return $custom;
        }

        return MODULES_PATH . "/_base/pdf/template.php";
    }

    protected function getTemplateData(array $rows): array
    {
        global $metadata;

        $meta = $metadata->__get($this->modulo);

        return [
            'rows' => $rows,
            'fields' => $meta['fields'],
            'moduleLabel' => $meta['label'],
        ];
    }
}
