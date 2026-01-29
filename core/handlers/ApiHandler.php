<?php

require_once MODULES_PATH . '/custom/reports/reports.php';


class ApiHandler
{
    protected DAO $dao;
    protected Metadata $metadata;
    protected Reports $report;

    public function __construct()
    {
        $this->dao      = new DAO();
        $this->metadata = new Metadata();
        $this->report = new Reports($this->metadata, $this->dao);
    }

    public function handle(array $context): array
    {
        $operation = $context['operation'] ?? null;

        if (!$operation) {
            return $this->error('Operação não definida', 400);
        }

        return match ($operation) {
            'search'       => $this->search($context),
            'get'          => $this->get($context),
            'getFields'    => $this->getFields($context),
            'getMetadata'  => $this->getMetadata($context),
            'runReport'    => $this->runReport($context),
            'saveReport'   => $this->saveReport($context),
            default        => $this->error('Operação não suportada', 400),
        };
    }

    protected function search(array $context): array
    {
        $q      = trim($context['get']['q'] ?? '');
        $field  = $context['get']['campo'] ?? null;
        $module = $context['get']['modulo'] ?? null;

        if (!$module || !$field || $q === '') {
            return $this->json([]);
        }

        $rows = $this->dao->search($module, $q, $field);

        $result = array_map(
            fn($row) => [
                'id'    => (int) $row['id'],
                'label' => $row[$field] ?? $row['id'],
            ],
            $rows
        );

        return $this->json($result);
    }


    protected function get(array $context): array
    {
        $module     = $context['get']['modulo'] ?? null;
        $id         = $context['get']['id'] ?? null;
        $labelField = $context['get']['labelField'] ?? null;

        if (!$module || !$id || !is_numeric($id)) {
            return $this->error('Módulo ou ID inválido', 400);
        }

        $record = $this->dao->select($module, (int)$id);

        if (!$record) {
            return $this->json(null, 404);
        }

        return $this->json([
            'id'    => (int)$record['id'],
            'label' => $record[$labelField] ?? $record['id']
        ]);
    }

    protected function getFields(array $context): array
    {
        $module = $context['get']['modulo'] ?? null;

        if (!$module) {
            return $this->error('Módulo não informado', 400);
        }

        return $this->json([
            'fields' => $this->metadata->getFieldsFromTable($module)
        ]);
    }

    protected function getMetadata(array $context): array
    {
        $module = $context['get']['modulo'] ?? null;

        if (!$module) {
            return $this->error('Módulo não informado', 400);
        }

        return $this->json([
            'metadata' => $this->metadata->__get($module)
        ]);
    }

    protected function runReport(array $context): array
    {
        if (!$context['json']) {
            return $this->error('JSON inválido', 400);
        }

        $result = $this->report->run($context['json'], ['limit' => 50]);
        return $this->json($result);
    }

    protected function saveReport(array $context): array
    {
        if (!$context['json']) {
            return $this->error('JSON inválido', 400);
        }

        $id = $context['json']['id'] ?? null;
        $saved = $this->report->save($context['json'], $id);

        return $this->json($saved);
    }

    protected function json(mixed $data, int $code = 200): array
    {
        return [
            'type' => 'json',
            'data' => $data,
            'code' => $code
        ];
    }

    protected function error(string $message, int $code): array
    {
        return [
            'type'    => 'error',
            'message' => $message,
            'code'    => $code
        ];
    }
}
