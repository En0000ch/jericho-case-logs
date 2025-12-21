<?php

namespace AnalyticsWP\Lib;

/**
 * ReactQueryBuilderParser Class
 * 
 * Parses JSON rules from react-querybuilder into SQL WHERE clauses for WordPress
 * 
 * @package YourPluginName
 */

class ReactQueryBuilderParser
{

    /**
     * Mapping of operators from react-querybuilder to SQL
     */
    private static $operatorMap = [
        '='           => '=',
        '!='          => '!=',
        '<'           => '<',
        '>'           => '>',
        '<='          => '<=',
        '>='          => '>=',
        'contains'    => 'LIKE',
        'beginsWith'  => 'LIKE',
        'endsWith'    => 'LIKE',
        'doesNotBeginWith' => 'NOT LIKE',
        'doesNotEndWith' => 'NOT LIKE',
        'doesNotContain' => 'NOT LIKE',
        'in'          => 'IN',
        'notIn'       => 'NOT IN',
        'between'     => 'BETWEEN',
        'notBetween'  => 'NOT BETWEEN',
        'null'      => 'IS NULL',
        'notNull'   => 'IS NOT NULL',
        'isEmpty'     => '= ""',
        'isNotEmpty'  => '!= ""',
    ];

    /**
     * Build a complete SQL WHERE clause from react-querybuilder JSON
     * 
     * @param array $rules The rules array from react-querybuilder
     * @param array $allowedFields Optional array of allowed field names (for security)
     * @return string The SQL WHERE clause
     */
    public static function buildWhereClauseFromQueryBuilderJson($rules, $allowedFields = [])
    {
        // Safety check
        if (empty($rules) || !isset($rules['combinator'])) {
            return "1=1"; // Default to TRUE if no rules
        }

        $sql = self::parseRuleGroup($rules, $allowedFields);

        // Ensure we have a valid WHERE clause
        if (empty($sql)) {
            return "1=1";
        }

        return $sql;
    }

    /**
     * Parse a rule group recursively
     * 
     * @param array $group The rule group
     * @param array $allowedFields Optional array of allowed field names
     * @return string The SQL for this group
     */
    private static function parseRuleGroup($group, $allowedFields = [])
    {
        if (!isset($group['combinator']) || !isset($group['rules']) || empty($group['rules'])) {
            return "";
        }

        $combinator = strtoupper($group['combinator']);
        $validCombinators = ['AND', 'OR'];

        if (!in_array($combinator, $validCombinators)) {
            $combinator = 'AND'; // Default to AND for safety
        }

        $conditions = [];

        foreach ($group['rules'] as $rule) {
            // Check if this is a nested rule group
            if (isset($rule['combinator']) && isset($rule['rules'])) {
                $nestedSql = self::parseRuleGroup($rule, $allowedFields);
                if (!empty($nestedSql)) {
                    $conditions[] = "($nestedSql)";
                }
            }
            // This is a simple rule
            elseif (isset($rule['field']) && isset($rule['operator'])) {
                $condition = self::parseRule($rule, $allowedFields);
                if (!empty($condition)) {
                    $conditions[] = $condition;
                }
            }
        }

        if (empty($conditions)) {
            return "";
        }

        return implode(" $combinator ", $conditions);
    }

    /**
     * Parse a single rule into SQL
     * 
     * @param array $rule The rule object
     * @param array $allowedFields Optional array of allowed field names
     * @return string The SQL for this rule
     */
    private static function parseRule($rule, $allowedFields = [])
    {
        global $wpdb;

        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = isset($rule['value']) ? $rule['value'] : null;

        // Security: validate field name if allowedFields is provided
        if (!empty($allowedFields) && !in_array($field, $allowedFields)) {
            return ""; // Skip rules with disallowed fields
        }

        // Sanitize the field name to prevent SQL injection
        $field = self::sanitizeFieldName($field);

        // Map the operator
        if (!isset(self::$operatorMap[$operator])) {
            return ""; // Invalid operator
        }
        $sqlOperator = self::$operatorMap[$operator];

        // Handle special operators that don't need values
        if ($operator === 'null') {
            return "$field IS NULL";
        } elseif ($operator === 'notNull') {
            return "$field IS NOT NULL";
        } elseif ($operator === 'isEmpty') {
            return "($field = '' OR $field IS NULL)";
        } elseif ($operator === 'isNotEmpty') {
            return "($field != '' AND $field IS NOT NULL)";
        }

        // For operators that need values, prepare the value
        if ($value === null) {
            return "";
        }

        // Handle array-based operators
        if ($operator === 'in' || $operator === 'notIn') {
            if (!is_array($value)) {
                $value = [$value];
            }

            if (empty($value)) {
                return $operator === 'in' ? "0=1" : "1=1";
            }

            $placeholders = [];
            foreach ($value as $val) {
                $placeholders[] = "'" . esc_sql($val) . "'";
            }

            return "$field $sqlOperator (" . implode(", ", $placeholders) . ")";
        }

        // Handle BETWEEN operators
        if ($operator === 'between' || $operator === 'notBetween') {
            if (!is_array($value) || count($value) < 2) {
                return "";
            }

            $min = "'" . esc_sql($value[0]) . "'";
            $max = "'" . esc_sql($value[1]) . "'";

            return "$field $sqlOperator $min AND $max";
        }

        // Handle LIKE operators with special formatting
        if ($operator === 'contains' || $operator === 'doesNotContain') {
            $escaped_value = esc_sql($value);
            return "$field $sqlOperator '%$escaped_value%'";
        } elseif ($operator === 'beginsWith') {
            $escaped_value = esc_sql($value);
            return "$field $sqlOperator '$escaped_value%'";
        } elseif ($operator === 'endsWith') {
            $escaped_value = esc_sql($value);
            return "$field $sqlOperator '%$escaped_value'";
        } elseif ($operator === 'doesNotBeginWith') {
            $escaped_value = esc_sql($value);
            return "$field $sqlOperator '$escaped_value%'";
        } elseif ($operator === 'doesNotEndWith') {
            $escaped_value = esc_sql($value);
            return "$field $sqlOperator '%$escaped_value'";
        }

        // Standard operators
        $escaped_value = esc_sql($value);
        return "$field $sqlOperator '$escaped_value'";
    }

    /**
     * Sanitize a field name to prevent SQL injection
     * 
     * @param string $field The field name to sanitize
     * @return string The sanitized field name
     */
    private static function sanitizeFieldName($field)
    {
        // Remove any dangerous characters
        $field = preg_replace('/[^a-zA-Z0-9_\.]/', '', $field);

        // If it contains a dot, treat it as table.column format
        if (strpos($field, '.') !== false) {
            list($table, $column) = explode('.', $field, 2);
            return "`" . trim($table) . "`.`" . trim($column) . "`";
        }

        // Just a column name
        return "`" . trim($field) . "`";
    }
}
