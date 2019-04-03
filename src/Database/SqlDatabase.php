<?php

namespace Arrilot\DataAnonymization\Database;

use PDO;

class SqlDatabase implements DatabaseInterface
{
    /**
     * PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Constructor.
     *
     * @param string     $dsn
     * @param string     $user
     * @param string     $password
     * @param null|array $options
     */
    public function __construct($dsn, $user, $password, $options = null)
    {
        if (is_null($options)) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
        }

        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    /**
     * Get all rows from a $table specified by where. Only $columns are selected.
     *
     * @param string      $table
     * @param string      $columns
     * @param string|null $where
     *
     * @return array
     */
    public function getRows($table, $columns, $where)
    {
        $columns = implode(',', $columns);
        $sql = "SELECT {$columns} FROM {$table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        $stmt = $this->pdo->query($sql);
        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }

    /**
     * Update $column value with a $newValue.
     * Update is performed on a row specified by $primaryValue of $table.
     *
     * @param string $table
     * @param array  $primaryKeyValue
     * @param string $column
     * @param mixed $value
     *
     * @return void
     */
    public function updateByPrimary($table, $primaryKeyValue, $column, $value)
    {
        $where = $this->buildWhereForArray($primaryKeyValue);
        $quotedValue = $value === null ? $value : $this->pdo->quote($value);

        $sql = "UPDATE
                    {$table}
                SET
                    {$column} = {$quotedValue}
                WHERE
                    {$where}";

        $this->pdo->query($sql);
    }

    /**
     * Build SQL where for key-value array.
     *
     * @param array $primaryKeyValue
     *
     * @return string
     */
    protected function buildWhereForArray($primaryKeyValue)
    {
        $where = [];
        foreach ($primaryKeyValue as $key => $value) {
            $where[] = "{$key}='{$value}'";
        }

        return implode(' AND ', $where);
    }
}
