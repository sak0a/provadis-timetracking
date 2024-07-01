<?php

namespace backend\database;


use Exception;

/**
 * created by Laurin Noel Frank
 */

class Filter
{
    private mixed $filters;

    /**
     * @param string $jsonFilters JSON string containing filters
     */
    public function __construct($jsonFilters)
    {
        if ($jsonFilters) {
            $this->filters = json_decode($jsonFilters, true);
        } else {
            $this->filters = [];
        }
    }

    /**
     * @param string $field Field name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     */
    public function addFilter($field, $operator, $value): void
    {
        if (!isset($this->filters[$field])) {
            $this->filters[$field] = [];
        }
        $this->filters[$field][$operator] = $value;
    }

    /**
     * @return mixed Filters
     */
    public function getFilters(): mixed
    {
        return $this->filters;
    }

    /**
     * @return bool True if there are filters, false otherwise
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    /**
     * @param string $tableName Table name
     * @return string converted filters as SQL clause
     */
    public function toSQL(string $tableName): string
    {
        $sql = "SELECT * FROM `$tableName`";
        if (!empty($this->filters)) {
            $sql .= " WHERE " . $this->parseFilters($this->filters);
        }
        return $sql;
    }


    /**
     * @return string converted filters as WHERE SQL clause
     */
    public function toWhereSQL(): string
    {
        $sql = " WHERE ";
        $sql .= $this->parseFilters($this->filters);
        return $sql;
    }

    /**
     * @param string $tableName Table name
     * @return string converted filters as COUNT SQL clause
     */
    public function toCountSQL(string $tableName): string
    {
        $sql = "SELECT COUNT(*) AS total FROM `$tableName` WHERE ";
        $sql .= $this->parseFilters($this->filters);
        return $sql;
    }

    /**
     * @param mixed $filters Filters to parse
     * @param string $parentOp Parent operator
     * @return string converted filters as SQL clause
     */
    private function parseFilters($filters, $parentOp = ''): string
    {
        $conditions = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                if ($this->isLogicalOperator($field)) {
                    // Logical operators
                    $operatorSql = $this->handleLogicalOperator($field, $value);
                    if ($parentOp === '$not') {
                        $conditions[] = "NOT ($operatorSql)";
                    } else {
                        $conditions[] = $operatorSql;
                    }
                } else {
                    // Comparison conditions
                    foreach ($value as $op => $val) {
                        $conditions[] = $this->parseCondition($field, $op, $val, $parentOp);
                    }
                }
            } else {
                // Simple equality
                $safeValue = $this->escape($value);
                $conditions[] = "`$field` = '$safeValue'";
            }
        }

        return implode(' AND ', $conditions);
    }

    /**
     * @param string $operator Logical operator
     * @param mixed $values Values to parse
     * @return string converted logical operator as SQL clause
     * @throws Exception if the operator is not supported
     */
    private function handleLogicalOperator($operator, $values): string
    {
        return match ($operator) {
            '$and' => '(' . implode(' AND ', array_map([$this, 'parseFilters'], $values)) . ')',
            '$or' => '(' . implode(' OR ', array_map([$this, 'parseFilters'], $values)) . ')',
            '$nor' => 'NOT (' . implode(' OR ', array_map([$this, 'parseFilters'], $values)) . ')',
            default => throw new Exception("Unsupported logical operator: $operator"),
        };
    }

    /**
     * @param string $field Field name
     * @param string $op Comparison operator
     * @param mixed $val Value to compare
     * @param string $parentOp Parent operator
     * @throws Exception if the operator is not supported
     */
    private function parseCondition(string $field, string $op, mixed $val, string $parentOp = ''): string
    {
        $safeValue = $this->escape($val);

        $condition = match ($op) {
            '$gt', '>' => "`$field` > '$safeValue'",
            '$lt', '<' => "`$field` < '$safeValue'",
            '$gte', '>=' => "`$field` >= '$safeValue'",
            '$lte', '<=' => "`$field` <= '$safeValue'",
            '$ne', '!=' => "`$field` != '$safeValue'",
            '$eq', '=' => "`$field` = '$safeValue'",
            '$li' => "`$field` LIKE '$safeValue'",
            default => throw new Exception("Unsupported operator: $op"),
        };

        if ($parentOp === '$not') {
            $condition = "NOT ($condition)";
        }

        return $condition;
    }

    /**
     * @param string $str String to check
     * @return bool True if the string is a logical operator, false otherwise
     */
    private function isLogicalOperator($str): bool
    {
        return in_array($str, ['$and', '$or', '$nor', '$not']);
    }

    /**
     * @param mixed $value Value to escape
     * @return mixed Escaped value
     */
    private function escape($value)
    {
        // Implement proper escaping here
        return addslashes($value);
    }
}

