<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */

require_once dirname(__DIR__) . '/core/dao/DAO.php';
require_once dirname(__DIR__) . '/core/services/log.php';

class Metadata
{
    private $dao;
    private $log;
    private $basePath;
    private $customPath;

    public function __construct()
    {
        $this->dao = new DAO();
        $this->basePath = __DIR__;
        $this->customPath = __DIR__ . '/custom';
        $this->log = new Log();
    }

    public function getTables()
    {
        $tables = $this->dao->whitelist();

        $this->log->debug("Tabelas encontradas.", [
            'tables' => $tables
        ]);

        return $tables;
    }

    public function getFieldsFromTable($table)
    {
        $this->log->debug("Carregando campos da tabela.", [
            'table' => $table
        ]);

        $fields = $this->dao->generateTable($table);

        $this->log->debug("Campos carregados.", [
            'table' => $table,
            'fields' => $fields,
        ]);

        return $fields;
    }

    public function getForeignKeyField(string $module, string $parentModule): ?string
    {
        $metadata = $this->__get($module);
        $fields = $metadata['fields'] ?? [];

        foreach ($fields as $fieldName => $info) {
            if (($info['related_module'] ?? null) === $parentModule) {
                return $fieldName;
            }
        }

        return null;
    }

    public function mapInputType($type)
    {
        $type = strtolower($type);

        if (strpos($type, 'tinyint(1)') !== false)
            return 'checkbox';
        if (strpos($type, 'int') !== false)
            return 'number';
        if (strpos($type, 'varchar') !== false)
            return 'text';
        if (strpos($type, 'text') !== false)
            return 'textarea';
        if (strpos($type, 'date') !== false)
            return 'date';
        if (strpos($type, 'decimal') !== false)
            return 'number';

        return 'text';
    }

    public function makeLabel($fieldName)
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }

    public function getForeignKeysFromTable(string $table): array
    {
        $createSql = $this->dao->getCreateTable($table);

        $fks = [];

        preg_match_all(
            '/CONSTRAINT `(.+?)`\s+FOREIGN KEY\s+\(`(.+?)`\)\s+REFERENCES\s+`(.+?)`\s+\(`(.+?)`\)(.*?)(?:,|\n\))/s',
            $createSql,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $extras = $m[5];

            preg_match('/ON DELETE (\w+)/', $extras, $onDelete);
            preg_match('/ON UPDATE (\w+)/', $extras, $onUpdate);

            $fks[$m[2]] = [
                'constraint' => $m[1],
                'related_module' => $m[3],
                'related_field' => $m[4],
                'on_delete' => $onDelete[1] ?? null,
                'on_update' => $onUpdate[1] ?? null,
            ];
        }

        return $fks;
    }

    public function resolveSearchField(array $fields): string
    {
        foreach ($fields as $field) {
            $type = strtolower($field['Type']);

            if (
                strpos($type, 'varchar') !== false ||
                strpos($type, 'text') !== false
            ) {
                return $field['Field'];
            }
        }

        foreach ($fields as $field) {
            if ($field['Field'] !== 'id') {
                return $field['Field'];
            }
        }

        return 'id';
    }


    public function mountFieldMetadata($fields, string $table)
    {
        $metadata = [];
        $count = 0;

        $foreignKeys = $this->getForeignKeysFromTable($table);

        foreach ($fields as $field) {
            $fieldName = $field['Field'];

            $fkInfo = $foreignKeys[$fieldName] ?? null;
            $related_fields = null;
            $searchField = null;

            if (!empty($fkInfo['related_module'])) {
                $related_fields = $this->dao->generateTable($fkInfo['related_module']);
                $searchField = $this->resolveSearchField($related_fields);
            }

            $metadata[$fieldName] = [
                'input' => $this->mapInputType($field['Type']),
                'default' => $field['Default'],
                'required' => $field['Null'] === 'NO',
                'label' => $this->makeLabel($fieldName),
                'related_module' => $fkInfo['related_module'] ?? null,
                'search_field' => $searchField,
                'related_fields' => $related_fields,
                'extra' => $field['Extra'],
                'order' => $count,
                'treatment' => 'show',
                'listview_treatment' => 'show',
                'foreign_key' => $fkInfo,
            ];

            $count++;
        }

        return $metadata;
    }

    public function clearMetadataFiles()
    {
        $files = glob(__DIR__ . '/*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                if (basename($file) === 'reports.json') {
                    continue;
                }
                unlink($file);
                $this->log->info("Arquivo de metadata deletado", ['file' => $file]);
            }
        }
    }

    public function generateRelationships(): void
    {
        $metadataFiles = glob($this->basePath . '/*.json');

        foreach ($metadataFiles as $childMetadataFile) {

            if (basename($childMetadataFile) === 'reports.json') {
                continue;
            }

            $childMetadata = json_decode(file_get_contents($childMetadataFile), true);
            $childModule = basename($childMetadataFile, '.json');

            foreach ($childMetadata['fields'] ?? [] as $fieldName => $fieldMetadata) {

                if (empty($fieldMetadata['foreign_key']['related_module'])) {
                    continue;
                }

                $parentModule = $fieldMetadata['foreign_key']['related_module'];
                $parentMetadataFile = $this->basePath . "/{$parentModule}.json";

                if (!file_exists($parentMetadataFile)) {
                    $this->log->warning('Metadata do módulo pai não encontrado', [
                        'parentModule' => $parentModule
                    ]);
                    continue;
                }

                $parentMetadata = json_decode(
                    file_get_contents($parentMetadataFile),
                    true
                );

                if (!isset($parentMetadata['items']) || !is_array($parentMetadata['items'])) {
                    $parentMetadata['items'] = [];
                }

                $index = null;

                foreach ($parentMetadata['items'] as $i => $item) {
                    if ($item['module'] === $childModule) {
                        $index = $i;
                        break;
                    }
                }

                if ($index === null) {
                    $parentMetadata['items'][] = [
                        'module' => $childModule,
                        'label' => ucfirst($childModule),
                        'enabled' => false,
                        'limit' => 10,
                        'relations' => []
                    ];
                    $index = array_key_last($parentMetadata['items']);
                }

                $relations = $parentMetadata['items'][$index]['relations'];

                foreach ($relations as $rel) {
                    if ($rel['foreign_key'] === $fieldName) {
                        continue 2;
                    }
                }

                $parentMetadata['items'][$index]['relations'][] = [
                    'foreign_key' => $fieldName,
                    'search_field' => $fieldMetadata['search_field'] ?? 'id',
                    'display_template' => null,
                ];

                file_put_contents(
                    $parentMetadataFile,
                    json_encode($parentMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $this->log->info('Relacionamento items criado', [
                    'parent' => $parentModule,
                    'child' => $childModule,
                    'foreign_key' => $fieldName
                ]);
            }
        }
    }

    public function generate()
    {
        $this->clearMetadataFiles();

        $tabelas = $this->getTables();
        $modulesData = [];

        foreach ($tabelas as $tabela) {
            $campos = $this->getFieldsFromTable($tabela);

            $metadata = [
                'label' => $tabela,
                'behavior' => 'default',
                'fields' => $this->mountFieldMetadata($campos, $tabela),
                'items' => [],
            ];

            file_put_contents(
                __DIR__ . "/$tabela.json",
                json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $modulesData[$tabela] = $metadata;
        }

        $this->generateRelationships();

        $this->generateReportsMetadata($modulesData);
        $this->cleanupOrphanCustomFiles();
    }

    public function cleanupOrphanCustomFiles(): void
    {
        if (!is_dir($this->customPath)) {
            return;
        }

        $validModules = array_map(
            fn($file) => pathinfo($file, PATHINFO_FILENAME),
            glob($this->basePath . '/*.json')
        );

        $customFiles = glob($this->customPath . '/*.json');

        foreach ($customFiles as $customFile) {
            $moduleName = pathinfo($customFile, PATHINFO_FILENAME);

            if (!in_array($moduleName, $validModules, true)) {
                unlink($customFile);
                $this->log->info("Custom metadata órfão removido.", ['file' => $customFile]);
            }
        }
    }

    public function mergeMetadata(array $base, array $custom): array
    {
        foreach ($custom as $key => $value) {

            if ($key === 'items' && is_array($value)) {
                $base['items'] = $this->mergeItems(
                    $base['items'] ?? [],
                    $value
                );
                continue;
            }


            if (is_array($value) && ($value['_remove'] ?? false)) {
                unset($base[$key]);
                continue;
            }

            if (
                isset($base[$key]) &&
                is_array($base[$key]) &&
                is_array($value)
            ) {
                $base[$key] = $this->mergeMetadata($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    public function getBaseMetadata(string $module): array
    {
        $file = $this->basePath . "/$module.json";

        if (!file_exists($file)) {
            throw new RuntimeException("Metadata base de {$module} não encontrado");
        }

        return json_decode(file_get_contents($file), true);
    }

    public function getCustomMetadata(string $module): ?array
    {
        $file = $this->customPath . "/$module.json";

        if (!file_exists($file)) {
            return null;
        }

        return json_decode(file_get_contents($file), true);
    }

    public function getMergedMetadata(string $module): array
    {
        $base = $this->getBaseMetadata($module);
        $custom = $this->getCustomMetadata($module);

        if ($custom) {
            $base = $this->mergeMetadata($base, $custom);
        }

        return $base;
    }

    public function __get($module)
    {
        return $this->getMergedMetadata($module);
    }


    public function getBehavior(string $module)
    {
        $metadata = $this->__get($module);

        if (empty($metadata['behavior']) || $metadata['behavior'] === 'default') {
            return null;
        }

        $behaviorName = $metadata['behavior'];

        if (!is_string($behaviorName)) {
            throw new RuntimeException(
                "Behavior do módulo {$module} deve ser uma string"
            );
        }

        $path = METADATA_PATH . "/behavior/{$behaviorName}.json";

        if (!is_file($path)) {
            throw new RuntimeException(
                "Arquivo de behavior '{$behaviorName}.json' não encontrado"
            );
        }

        $data = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "Behavior '{$behaviorName}' possui JSON inválido"
            );
        }

        return [
            'module' => $module,
            'name' => $behaviorName,
            'rules' => $data
        ];
    }

    public function buildFieldsSection(array $modulesData): array
    {
        $fields = [];
        foreach ($modulesData as $moduleName => $module) {
            foreach (($module['fields'] ?? []) as $fieldName => $field) {
                $fields[$moduleName][$fieldName] = [
                    'label' => $field['label'] ?? $this->makeLabel($fieldName),
                    'type' => $field['input'] ?? 'string',
                    'required' => $field['required'] ?? false,
                    'foreign_key' => $field['foreign_key'] ?? null,
                    'related_module' => $field['related_module'] ?? null,
                    'description' => $field['description'] ?? '',
                ];
            }
        }
        return $fields;
    }

    public function buildSources(array $modulesData): array
    {
        $sources = [];
        foreach ($modulesData as $moduleName => $module) {
            $sources[$moduleName] = [
                'label' => $module['label'] ?? ucfirst($moduleName),
            ];
        }
        return $sources;
    }

    public function buildJoinsSection(array $modulesData): array
    {
        $joins = [];

        foreach ($modulesData as $moduleName => $module) {

            foreach (($module['fields'] ?? []) as $fieldName => $field) {

                if (empty($field['foreign_key']['related_module'])) {
                    continue;
                }

                $parent = $field['foreign_key']['related_module'];
                $parentField = $field['foreign_key']['related_field'] ?? 'id';

                $joins[$moduleName][] = [
                    'module' => $parent,
                    'label' => "Relacionamento com {$parent}",
                    'enabled' => false,
                    'join' => "LEFT JOIN {$parent} 
                           ON {$moduleName}.{$fieldName} = {$parent}.{$parentField}",
                ];
            }
        }

        return $joins;
    }

    public function buildDefaults(): array
    {
        return [
            'limit' => 1000,
            'ui' => [
                'allow_export' => true,
                'allow_graph' => true,
                'page_size' => 50,
                'searchable' => true,
            ],
        ];
    }

    public function generateReportsMetadata(array $modulesData)
    {
        $metadata = [
            'label' => 'Relatórios',
            'type' => 'virtual',
            'engine' => 'sql',
            'customModules' => false,
            'schema' => [
                'required' => ['from', 'select'],
                'optional' => [
                    'joins',
                    'where',
                    'group_by',
                    'order_by',
                    'limit',
                    'filters',
                    'formatting',
                    'ui',
                    'export',
                    'permissions',
                    'events',
                    'variables'
                ],
            ],
            'fields' => $this->buildFieldsSection($modulesData),
            'sources' => $this->buildSources($modulesData),
            'params' => [
                'from' => [
                    'label' => 'Tabela base',
                    'type' => 'array',
                    'description' => 'Nome da tabela principal do relatório',
                    'required' => true,
                ],
                'select' => [
                    'label' => 'Colunas',
                    'type' => 'array',
                    'item_type' => 'string',
                    'description' => 'Lista de campos ou expressões SQL',
                    'required' => true,
                ],
                'relations' => $this->buildJoinsSection($modulesData),
            ],
            'defaults' => $this->buildDefaults(),
        ];

        file_put_contents(
            $this->basePath . '/reports.json',
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->log->info('Metadata de relatórios gerado/atualizado');
    }

    private function mergeItems(array $baseItems, array $customItems): array
    {
        $indexed = [];

        foreach ($baseItems as $item) {
            if (!isset($item['module'])) continue;
            $indexed[$item['module']] = $item;
        }

        foreach ($customItems as $module => $customConfig) {
            if (!isset($indexed[$module])) {
                continue;
            }

            foreach ($customConfig as $k => $v) {
                $indexed[$module][$k] = $v;
            }
        }

        return array_values($indexed);
    }
}
