<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use PDO;
use Roulette\Query\Operation;

/**
 * Schema migration generator. Compares model prototype declarations against the
 * live DB schema and generates DDL to bring the DB into sync.
 *
 * The model prototype IS the source of truth — no separate migration files needed.
 *
 * Usage:
 *   Schema::sql(User::class);          // generate CREATE TABLE DDL (no DB needed)
 *   Schema::diff(User::class);         // compare prototype vs live DB
 *   Schema::migrate(User::class);      // create table or add missing columns
 *
 * Requires a PDO-based tunel registered via Operation::setOperationTunel() or
 * auto-detected from the active framework adapter.
 *
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Schema
{
    private static array $typeMap = [
        'sqlite' => [
            'string'   => 'TEXT',
            'integer'  => 'INTEGER',
            'float'    => 'REAL',
            'double'   => 'REAL',
            'numeric'  => 'NUMERIC',
            'boolean'  => 'INTEGER',
            'email'    => 'TEXT',
            'url'      => 'TEXT',
            'uuid'     => 'TEXT',
            'date'     => 'TEXT',
            'datetime' => 'TEXT',
            'time'     => 'TEXT',
        ],
        'mysql' => [
            'string'   => 'VARCHAR(255)',
            'integer'  => 'INT',
            'float'    => 'FLOAT',
            'double'   => 'DOUBLE',
            'numeric'  => 'DECIMAL(10,2)',
            'boolean'  => 'TINYINT(1)',
            'email'    => 'VARCHAR(255)',
            'url'      => 'VARCHAR(255)',
            'uuid'     => 'CHAR(36)',
            'date'     => 'DATE',
            'datetime' => 'DATETIME',
            'time'     => 'TIME',
        ],
    ];

    /**
     * Generate a CREATE TABLE DDL string from the model prototype.
     * Does not require a database connection.
     *
     * @param string $modelClass  Fully-qualified model class name
     * @param string $dialect     'sqlite' (default) | 'mysql'
     * @return string             SQL CREATE TABLE statement
     */
    public static function sql(string $modelClass, string $dialect = 'sqlite'): string
    {
        $table = $modelClass::getTable();
        $cols  = static::extractModelColumns($modelClass);
        return static::buildCreateTable($table, $cols, $dialect);
    }

    /**
     * Compare the model prototype against the live DB schema.
     *
     * Returns an array with:
     * - table   : table name
     * - exists  : whether the table already exists in DB
     * - missing : columns present in prototype but absent in DB (→ need ADD COLUMN)
     * - extra   : columns present in DB but absent in prototype (info only, never dropped)
     *
     * @param string $modelClass  Fully-qualified model class name
     * @return array{table:string, exists:bool, missing:array, extra:array}
     */
    public static function diff(string $modelClass): array
    {
        $table      = $modelClass::getTable();
        $modelCols  = static::extractModelColumns($modelClass);
        $dbCols     = static::fetchDbColumns($table);
        $dbNames    = array_column($dbCols, 'name');
        $modelNames = array_column($modelCols, 'name');

        $missing = array_values(
            array_filter($modelCols, fn($c) => !in_array($c['name'], $dbNames))
        );
        $extra = array_values(
            array_map(
                fn($c) => ['name' => $c['name']],
                array_filter($dbCols, fn($c) => !in_array($c['name'], $modelNames))
            )
        );

        return [
            'table'   => $table,
            'exists'  => !empty($dbCols),
            'missing' => $missing,
            'extra'   => $extra,
        ];
    }

    /**
     * Apply schema changes to the live DB.
     * Creates the table if it doesn't exist; otherwise adds only the missing columns.
     * Never drops columns or changes existing column types.
     *
     * @param string $modelClass  Fully-qualified model class name
     */
    public static function migrate(string $modelClass): void
    {
        $table     = $modelClass::getTable();
        $dialect   = static::detectDialect();
        $modelCols = static::extractModelColumns($modelClass);
        $dbCols    = static::fetchDbColumns($table);
        $pdo       = static::getPdo();

        if (empty($dbCols)) {
            $pdo->exec(static::buildCreateTable($table, $modelCols, $dialect));
            return;
        }

        $dbNames = array_column($dbCols, 'name');
        foreach ($modelCols as $col) {
            if (!in_array($col['name'], $dbNames)) {
                $pdo->exec("ALTER TABLE $table ADD COLUMN " . static::columnDdl($col, $dialect));
            }
        }
    }

    // --- Private Helpers ---

    /**
     * Extract normalized column definitions from the model prototype.
     * Each column carries: name, type, nullable, default, primary.
     */
    private static function extractModelColumns(string $modelClass): array
    {
        $primary = $modelClass::getPrimary();
        $cols    = [];

        foreach ($modelClass::getFields() as $field) {
            if ($field->isComputed()) continue;
            $cols[] = [
                'name'     => $field->getSource(),
                'type'     => (string) ($field->getConfig('type') ?? ''),
                'nullable' => (bool) ($field->getConfig('nullable') ?? true),
                'default'  => $field->getDefault(),
                'primary'  => $field->getName() === $primary,
            ];
        }

        return $cols;
    }

    /**
     * Fetch column metadata for an existing table from the live DB.
     * Returns empty array if the table does not exist.
     */
    private static function fetchDbColumns(string $table): array
    {
        try {
            $rows = static::getPdo()
                ->query("PRAGMA table_info($table)")
                ->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get the PDO connection from the active tunel.
     * @throws \RuntimeException if the connection is not PDO.
     */
    private static function getPdo(): PDO
    {
        $conn = Operation::getOperationTunel()->getConnection();
        if (!($conn instanceof PDO)) {
            throw new \RuntimeException(
                'Schema requires a PDO-based tunel. Set one via Operation::setOperationTunel().'
            );
        }
        return $conn;
    }

    /**
     * Detect the SQL dialect from the active PDO connection driver.
     */
    private static function detectDialect(): string
    {
        try {
            return match(static::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME)) {
                'mysql'  => 'mysql',
                default  => 'sqlite',
            };
        } catch (\Throwable) {
            return 'sqlite';
        }
    }

    /**
     * Map an ORM type name to a SQL column type for the given dialect.
     * Falls back to TEXT for unknown types.
     */
    private static function mapType(string $ormType, string $dialect): string
    {
        return static::$typeMap[$dialect][$ormType]
            ?? static::$typeMap['sqlite'][$ormType]
            ?? 'TEXT';
    }

    /**
     * Build a single column definition string for use in CREATE TABLE or ADD COLUMN.
     */
    private static function columnDdl(array $col, string $dialect): string
    {
        $name    = $col['name'];
        $sqlType = static::mapType($col['type'], $dialect);
        $ddl     = "$name $sqlType";

        if ($col['primary']) {
            $ddl .= ' PRIMARY KEY';
        } elseif (!$col['nullable']) {
            $ddl .= ' NOT NULL';
        }

        if ($col['default'] !== null && !$col['primary']) {
            $default = is_string($col['default']) ? "'{$col['default']}'" : $col['default'];
            $ddl    .= " DEFAULT $default";
        }

        return $ddl;
    }

    /**
     * Build a complete CREATE TABLE statement.
     */
    private static function buildCreateTable(string $table, array $cols, string $dialect): string
    {
        $colDefs = array_map(fn($col) => static::columnDdl($col, $dialect), $cols);
        return "CREATE TABLE $table (" . implode(', ', $colDefs) . ')';
    }
}
