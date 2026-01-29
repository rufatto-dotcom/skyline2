<?php

class StudioHandler
{
    protected Studio $studio;
    protected Metadata $metadata;
    protected DAO $dao;

    public function __construct()
    {
        $this->metadata = new Metadata();
        $this->dao      = new DAO();

        require_once ROOT_PATH . '/studio/studio.php';
        $this->studio   = new Studio($this->metadata);
    }

    public function handle(array $context): ?array
    {
        $operation = $context['operation'] ?? null;

        return match ($operation) {
            'saveMetadata' => $this->saveMetadata($context),
            'delete'       => $this->deleteEntity($context),
            default        => null,
        };
    }

    protected function saveMetadata(array $context): array
    {
        if ($context['method'] !== 'POST') {
            return $this->error('Método não permitido', 405);
        }

        $entity = $context['request']['entity'] ?? null;
        if (!$entity) {
            return $this->error('Entity não informada', 400);
        }

        $custom = $this->studio->buildCustomMetadata($entity, $context['post']);
        $path   = METADATA_PATH . "/custom/{$entity}.json";

        if (!empty($custom)) {
            file_put_contents($path, json_encode($custom, JSON_PRETTY_PRINT));
        } elseif (file_exists($path)) {
            unlink($path);
        }

        return $this->redirect("index.php?modulo=studio&entity={$entity}");
    }

    protected function deleteEntity(array $context): array
    {
        if ($context['method'] !== 'GET') {
            return $this->error('Método não permitido', 405);
        }

        $entity = $context['request']['entity'] ?? null;
        if (!$entity) {
            return $this->error('Entity não informada', 400);
        }

        $deleted = $this->dao->dropTable($entity);

        if (!$deleted) {
            return $this->error("Falha ao deletar a tabela '{$entity}'. Verifique os logs.", 500);
        }

        try {
            require_once CONFIG_PATH . '/actions/generateFunctions.php';
            regenerateMetadata();
            regenerateModules();
        } catch (Throwable $e) {
            error_log("Regeneração falhou após deletar {$entity}: " . $e->getMessage());
            return $this->error("Erro ao atualizar o sistema após exclusão. Verifique permissões.", 500);
        }

        return $this->redirect("index.php?modulo=studio");
    }

    protected function redirect(string $location): array
    {
        return [
            'type'     => 'redirect',
            'location' => $location
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

    protected function loadModules(): array
    {
        $modules = [];

        foreach (['modules'] as $base) {
            $path = ROOT_PATH . '/' . $base;

            foreach (scandir($path) as $dir) {
                if ($dir[0] === '.') continue;
                if (!is_dir("$path/$dir")) continue;
                if ($base === 'modules' && $dir === 'custom') continue;

                $modules[] = $dir;
            }
        }

        return array_unique($modules);
    }
}
