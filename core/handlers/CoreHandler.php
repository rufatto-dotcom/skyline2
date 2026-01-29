<?php

class CoreHandler
{
    protected DAO $dao;
    protected Metadata $metadata;

    public function __construct()
    {
        $this->dao = new DAO();
        $this->metadata = new Metadata();
    }

    public function handle(array $context): ?array
    {
        $operation = $context['operation'] ?? null;

        if (!$operation) {
            return null;
        }

        switch ($operation) {
            case 'save':
                return $this->handleSave($context);

            case 'delete':
                return $this->handleDelete($context);

            default:
                return [
                    'type' => 'error',
                    'code' => 400,
                    'message' => 'Operação inválida'
                ];
        }
    }

    protected function handleSave(array $context): array
    {
        if ($context['method'] !== 'POST') {
            return [
                'type' => 'error',
                'code' => 405,
                'message' => 'Método não permitido'
            ];
        }

        $modulo = $context['get']['modulo'] ?? null;
        $id     = $context['get']['id'] ?? null;
        $data   = $context['post'];

        $itemsByModule = $data['items'] ?? [];
        unset($data['items']);

        $parentId = $id
            ? $this->dao->update($modulo, $data)
            : $this->dao->insertInto($modulo, $data);

        $this->syncItems($modulo, $parentId, $itemsByModule);

        return $this->redirectResponse($modulo, $parentId, $context);
    }

    protected function handleDelete(array $context): array
    {

        if ($context['method'] !== 'POST') {
            return [
                'type' => 'error',
                'code' => 405,
                'message' => 'Método não permitido'
            ];
        }

        $modulo = $context['get']['modulo'] ?? null;
        $id     = $context['get']['id'] ?? null;

        $this->dao->delete($modulo, $id);

        return $this->redirectResponse($modulo, null, $context);
    }

    protected function syncItems(string $parentModule, int $parentId, array $itemsByModule): void
    {
        if (empty($itemsByModule)) {
            return;
        }

        foreach ($itemsByModule as $childModule => $items) {

            foreach ($items as $key => $item) {

                if ($key === '__INDEX__') {
                    continue;
                }

                if (!empty($item['deleted'])) {
                    if (!empty($item['id'])) {
                        $this->dao->delete($childModule, $item['id']);
                    }
                    continue;
                }

                // garante vínculo com o pai
                if (!isset($item[$parentModule . '_id']) && !isset($item['parent_id'])) {
                    // se você tiver um padrão fixo, aplique aqui
                }

                if (!empty($item['id'])) {
                    unset($item['deleted']);
                    $this->dao->update($childModule, $item);
                } else {
                    unset($item['deleted']);
                    $this->dao->insertInto($childModule, $item);
                }
            }
        }
    }

    protected function redirectResponse(string $modulo, ?int $id, array $context): array
    {
        $redirect = $context['get']['redirect'] ?? null;

        if (in_array($redirect, ['detail', 'DetailView'], true)) {
            return [
                'type' => 'redirect',
                'location' => "index.php?modulo={$modulo}&action=DetailView&id={$id}"
            ];
        }

        return [
            'type' => 'redirect',
            'location' => "index.php?modulo={$modulo}"
        ];
    }
}
