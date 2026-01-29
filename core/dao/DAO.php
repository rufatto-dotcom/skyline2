<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */


require_once dirname(__DIR__, 1) . '/services/log.php';

class DAO // Data Acess Object
{
    private Log $log;
    private PDO $pdo;

    public function __construct()
    {
        $this->log = new Log();
        $this->pdo = require dirname(__DIR__, 1) . '/config/database.connection.php';
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function whitelist()
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            return $tables ?: [];
        } catch (Exception $e) {

            $this->log->error("Erro ao carregar whitelist de tabelas", [
                'exception' => $e->getMessage()
            ]);

            return [];
        }
    }

    private function validateTable(string $modulo): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $modulo)) {

            $this->log->warning("Tentativa de acessar tabela inválida", [
                'modulo' => $modulo
            ]);

            return false;
        }

        $tables = $this->whitelist();
        $valid = in_array($modulo, $tables, true);

        return $valid;
    }

    public function generateTable($modulo)
    {

        if (!$this->validateTable($modulo)) {
            $this->log->error("generateTable: tabela inválida", [
                'modulo' => $modulo
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        try {
            $stmt = $this->pdo->query("DESCRIBE $modulo");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $colunas ?: [];
        } catch (Exception $e) {

            $this->log->error("Erro ao descrever tabela", [
                'modulo' => $modulo,
                'exception' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function insertInto(string $modulo, array $data)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("insertInto: tabela inválida", [
                'modulo' => $modulo,
                'data' => $data
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        $this->log->info("Tentando inserir registro", [
            'modulo' => $modulo,
            'data' => $data
        ]);

        try {
            $colunas = $this->generateTable($modulo);
            $fields = array_column($colunas, 'Field');

            $fields = array_filter($fields, fn($f) => $f !== 'id');
            $fields = array_values($fields);

            $campos = implode(', ', $fields);
            $placeholders = implode(', ', array_fill(0, count($fields), '?'));
            $valores = array_map(fn($f) => $data[$f] ?? null, $fields);

            $sql = "INSERT INTO $modulo ($campos) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($valores);

            $lastId = $this->pdo->lastInsertId();
            $this->log->info("Registro inserido com sucesso", [
                'modulo' => $modulo,
                'id' => $lastId
            ]);

            return $lastId;
        } catch (Exception $e) {
            $this->log->error("Falha ao inserir registro", [
                'modulo' => $modulo,
                'data' => $data,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function select($modulo, $id)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("select: tabela inválida", [
                'modulo' => $modulo,
                'id' => $id
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        $sql = "SELECT * FROM $modulo WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            $this->log->debug("SELECT executado", [
                'sql' => $sql,
                'params' => [$id],
                'resultado' => $result
            ]);

            return $result;
        } catch (Exception $e) {
            $this->log->error("Erro no SELECT", [
                'sql' => $sql,
                'params' => [$id],
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function selectAll(string $modulo)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("selectAll: tabela inválida", ['modulo' => $modulo]);
            throw new Exception('Esta tabela não é valida.');
        }

        try {
            $result = $this->pdo->query("SELECT * FROM `$modulo`");

            $this->log->debug("SELECT ALL executado", [
                'modulo' => $modulo
            ]);

            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log->error("Erro no SELECT ALL", [
                'modulo' => $modulo,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function delete($modulo, $id)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("delete: tabela inválida", [
                'modulo' => $modulo,
                'id' => $id
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        $this->log->info("Tentando deletar registro", [
            'modulo' => $modulo,
            'id' => $id
        ]);

        try {
            $sql = "DELETE FROM $modulo WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            $affected = $stmt->rowCount();

            $this->log->info("DELETE executado", [
                'modulo' => $modulo,
                'id' => $id,
                'linhas_afetadas' => $affected
            ]);

            return $affected > 0;
        } catch (Exception $e) {
            $this->log->error("Erro ao deletar registro", [
                'modulo' => $modulo,
                'id' => $id,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function update(string $modulo, array $data)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("update: tabela inválida", [
                'modulo' => $modulo,
                'data' => $data
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        $this->log->info("Tentando atualizar registro", [
            'modulo' => $modulo,
            'data' => $data
        ]);

        try {
            $colunas = $this->generateTable($modulo);
            $fields = array_column($colunas, 'Field');

            $setParts = [];
            $values = [];

            foreach ($fields as $field) {
                if ($field === 'id' || $field === 'created_at')
                    continue;

                if (!array_key_exists($field, $data) && $field !== 'updated_at') {
                    continue;
                }

                $value = $data[$field] ?? null;

                $setParts[] = "$field = ?";
                $values[] = $value;
            }

            $values[] = $data['id'];

            $updateString = implode(', ', $setParts);
            $sql = "UPDATE $modulo SET $updateString WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            $this->log->info("UPDATE resultado", [
                'modulo' => $modulo,
                'id' => $data['id'],
                'rows' => $stmt->rowCount(),
                'sql' => $sql,
                'values' => $values,
            ]);

            return $data['id'];
        } catch (Exception $e) {
            $this->log->error("Erro ao atualizar registro", [
                'modulo' => $modulo,
                'data' => $data,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function selectFromParent(string $modulo, string $fkField, $parentId)
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("selectFromParent: tabela inválida", [
                'modulo' => $modulo,
                'fkField' => $fkField,
                'parentId' => $parentId
            ]);
            throw new Exception('Esta tabela não é valida.');
        }

        $sql = "SELECT * FROM `$modulo` WHERE `$fkField` = ?";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([$parentId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->log->debug("selectFromParent executado", [
                'sql' => $sql,
                'params' => [$parentId],
                'resultadoCount' => count($result)
            ]);

            return $result;
        } catch (Exception $e) {
            $this->log->error("Erro no selectFromParent", [
                'sql' => $sql,
                'params' => [$parentId],
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getCreateTable(string $table)
    {
        if (!$this->validateTable($table)) {
            $this->log->error("Tabela inválida", []);
            throw new Exception('Esta tabela não é valida.');
        }

        $stmt = $this->pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['Create Table'] ?? '';
    }


    public function search(string $table, string $term, string $searchField): array
    {
        if (!$this->validateTable($table)) {
            throw new Exception("Tabela inválida: {$table}");
        }

        $sql = "SELECT * FROM `$table` WHERE `$searchField` LIKE ? LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%{$term}%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function selectPage(string $modulo, int $page = 1, int $perPage = 20): array
    {
        if (!$this->validateTable($modulo)) {
            $this->log->error("selectPage: tabela inválida", ['modulo' => $modulo]);
            throw new Exception('Esta tabela não é valida.');
        }

        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100)); // hard cap

        $offset = ($page - 1) * $perPage;

        try {
            $sql = "SELECT * FROM `$modulo` LIMIT $perPage OFFSET $offset";
            $stmt = $this->pdo->query($sql);

            $this->log->debug("SELECT PAGE executado", [
                'modulo' => $modulo,
                'page' => $page,
                'perPage' => $perPage,
                'offset' => $offset
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log->error("Erro no selectPage", [
                'modulo' => $modulo,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function countAll(string $modulo): int
    {
        if (!$this->validateTable($modulo)) {
            throw new Exception('Esta tabela não é valida.');
        }

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `$modulo`");
        return (int) $stmt->fetchColumn();
    }

    public function rawSelect(string $sql): array
    {
        try {
            $this->log->debug("RAW SELECT executado", [
                'sql' => $sql
            ]);

            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log->error("Erro no RAW SELECT", [
                'sql' => $sql,
                'exception' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function quote($value): string
    {
        return $this->pdo->quote($value);
    }

    public function createTable(array $data): bool
    {
        $table_name = trim($data['table_name'] ?? '');
        if (!$table_name || !preg_match('/^[a-z_][a-z0-9_]*$/i', $table_name)) {
            return false;
        }

        if ($this->tableExists($table_name)) {
            return true; // ou lançar exceção?
        }

        $columns = [];
        foreach ($data['fields'] ?? [] as $fieldData) {
            $col = $this->buildColumnDefinition($fieldData);
            if ($col) $columns[] = $col;
        }

        if (empty($columns)) return false;

        $pk = isset($data['fields']['id']) ? ', PRIMARY KEY (`id`)' : '';
        $sql = "CREATE TABLE `$table_name` (\n    " . implode(",\n    ", $columns) . "$pk\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $this->pdo->exec($sql);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela '$table_name': " . $e->getMessage());
            return false;
        }
    }

    public function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM information_schema.tables 
         WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $stmt->execute([$table]);
        return $stmt->fetch() !== false;
    }

    private function buildColumnDefinition(array $fieldData): ?string
    {
        $name = $fieldData['name'] ?? '';
        if (!$name || !preg_match('/^[a-z_][a-z0-9_]*$/i', $name)) {
            return null;
        }

        $type = $fieldData['type'] ?? 'varchar';
        $lengthRaw = trim($fieldData['length'] ?? '');
        $required = !empty($fieldData['required']);
        $autoInc = !empty($fieldData['auto_increment']);

        $length = null;
        if ($lengthRaw !== '' && ctype_digit($lengthRaw) && (int)$lengthRaw > 0) {
            $length = (int)$lengthRaw;
        }

        switch ($type) {
            case 'int':
                $sqlType = 'INT';
                break;
            case 'boolean':
                $sqlType = 'TINYINT(1)';
                break;
            case 'varchar':
                $sqlType = "VARCHAR(" . ($length ?: 255) . ")";
                break;
            case 'text':
                $sqlType = 'TEXT';
                break;
            case 'decimal':
                $sqlType = 'DECIMAL(10,2)';
                break;
            case 'date':
                $sqlType = 'DATE';
                break;
            case 'datetime':
                $sqlType = 'DATETIME';
                break;
            default:
                $sqlType = 'VARCHAR(255)';
        }

        $null = $required ? 'NOT NULL' : 'NULL';
        $auto = $autoInc ? 'AUTO_INCREMENT' : '';

        return "`$name` $sqlType $null $auto";
    }

    public function alterTableAddColumn(string $table, array $fieldData): bool
    {
        if (!$this->validateTable($table)) {
            return false;
        }

        $colDef = $this->buildColumnDefinition($fieldData);
        if (!$colDef) return false;

        $sql = "ALTER TABLE `$table` ADD COLUMN $colDef";

        try {
            $this->pdo->exec($sql);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao adicionar coluna em '$table': " . $e->getMessage());
            return false;
        }
    }

    public function alterTableDropColumn(string $table, string $columnName): bool
    {
        if (!$this->validateTable($table)) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $columnName)) {
            return false;
        }

        // Verifica se a coluna existe
        $stmt = $this->pdo->prepare("DESCRIBE `$table`");
        $stmt->execute();
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($columnName, $cols)) {
            return false;
        }

        $sql = "ALTER TABLE `$table` DROP COLUMN `$columnName`";

        try {
            $this->pdo->exec($sql);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao remover coluna '$columnName' de '$table': " . $e->getMessage());
            return false;
        }
    }

    public function dropTable(string $table): bool
    {
        if (!$this->validateTable($table)) {
            $this->log->error("dropTable: tentativa de deletar tabela inválida", [
                'table' => $table
            ]);
            return false;
        }

        $this->log->warning("Tentando DROPAR a tabela", [
            'table' => $table
        ]);

        try {
            $sql = "DROP TABLE `$table`";
            $this->pdo->exec($sql);

            $this->log->info("Tabela dropada com sucesso", [
                'table' => $table
            ]);

            return true;
        } catch (Exception $e) {
            $this->log->error("Erro ao dropar tabela", [
                'table' => $table,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }
}
