<?php

class Studio
{
    private $metadata;

    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadataMerged($entity)
    {
        return $this->metadata->getMergedMetadata($entity);
    }

    public function getMetadataDefault($entity)
    {
        return $this->metadata->getBaseMetadata($entity);
    }

    public function getMetadataCustom($entity)
    {
        return $this->metadata->getCustomMetadata($entity);
    }

    public function getItemsMerged($entity): array
    {
        return $this->indexByModule(
            $this->metadata->getMergedMetadata($entity)['items'] ?? []
        );
    }

    public function getItemsBase($entity): array
    {
        return $this->indexByModule(
            $this->metadata->getBaseMetadata($entity)['items'] ?? []
        );
    }

    public function getItemsCustom($entity): array
    {
        return $this->metadata->getCustomMetadata($entity)['items'] ?? [];
    }

    public function getEntityFields($entity)
    {
        $base   = $this->metadata->getBaseMetadata($entity)['fields'] ?? [];
        $custom = $this->metadata->getCustomMetadata($entity)['fields'] ?? [];
        $merged = $this->metadata->getMergedMetadata($entity)['fields'] ?? [];

        foreach ($merged as $fieldName => &$field) {
            $field['origin'] = isset($custom[$fieldName]) ? 'custom' : 'base';
        }

        return $merged;
    }

    public function getItems($entity): array
    {
        $merged = $this->getItemsMerged($entity);
        $custom = $this->getItemsCustom($entity);

        foreach ($merged as $module => &$item) {
            $item['origin'] = isset($custom[$module]) ? 'custom' : 'base';
        }

        return $merged;
    }


    public function getEntityLabel($entity): string
    {
        $base = $this->getMetadataDefault($entity);
        $custom = $this->getMetadataCustom($entity);
        $merged = $this->getMetadataMerged($entity);

        if (!empty($custom['label'])) {
            return $custom['label'];
        }

        if (!empty($merged['label'])) {
            return $merged['label'];
        }

        if (!empty($base['label'])) {
            return $base['label'];
        }

        return $entity;
    }


    public function buildCustomMetadata(string $entity, array $post): array
    {
        $base = $this->metadata->getBaseMetadata($entity);
        $custom = $this->metadata->getCustomMetadata($entity) ?? [];

        if (isset($post['label'])) {
            $baseLabel = $base['label'] ?? $entity;
            $postedLabel = trim($post['label']);

            if ($postedLabel !== $baseLabel) {
                $custom['label'] = $postedLabel;
            }
        }

        if (!empty($post['items'])) {
            $baseItems = $base['items'] ?? [];
            $custom['items'] = $this->buildItemsMetadata($post['items'], $baseItems);
        }


        foreach ($post['fields'] as $fieldName => $postedField) {
            if (!isset($base['fields'][$fieldName])) {
                continue;
            }

            foreach ($postedField as $key => $value) {
                $baseValue = $base['fields'][$fieldName][$key] ?? null;

                if ($value !== $baseValue) {
                    $custom['fields'][$fieldName][$key] = $value;
                }
            }

            if (empty($custom['fields'][$fieldName])) {
                unset($custom['fields'][$fieldName]);
            }
        }

        return empty($custom['fields']) && empty($custom['items'])
            ? []
            : $custom;
    }

    public function buildItemsMetadata(array $items, array $baseItems): array
    {
        $result = [];
        $baseIndexed = $this->indexByModule($baseItems);

        foreach ($items as $module => $item) {
            if (!isset($baseIndexed[$module])) {
                continue;
            }

            $base = $baseIndexed[$module];
            $diff = [];

            if ((bool)$item['enabled'] !== (bool)$base['enabled']) {
                $diff['enabled'] = (bool)$item['enabled'];
            }

            if (($item['label'] ?? null) !== ($base['label'] ?? null)) {
                $diff['label'] = trim($item['label']);
            }

            if ($diff) {
                $result[$module] = $diff;
            }
        }

        return $result;
    }

    private function indexByModule(array $items): array
    {
        $indexed = [];

        foreach ($items as $item) {
            if (empty($item['module'])) {
                continue;
            }

            $indexed[$item['module']] = $item;
        }

        return $indexed;
    }


    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (empty($item['module'])) {
                continue;
            }

            if (empty($item['relations'])) {
                $key = $item['module'] . ':';

                $normalized[$key] = array_merge($item, [
                    'foreign_key' => null,
                ]);

                continue;
            }

            foreach ($item['relations'] as $rel) {
                $fk = $rel['foreign_key'] ?? null;
                if (!$fk) {
                    continue;
                }

                $key = $item['module'] . ':' . $fk;

                $normalized[$key] = array_merge($item, [
                    'foreign_key' => $fk,
                ]);
            }
        }

        return $normalized;
    }
}
