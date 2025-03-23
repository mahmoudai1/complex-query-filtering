<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class JobFilterService
{
    public function customFilterParser(string $filterString): array
    {
        $filters = [];
        $conditions = $this->splitConditions($filterString);
        $logicalOperator = null;

        foreach ($conditions as $condition) {
            $condition = trim($condition);

            if (in_array($condition, ['AND', 'OR'])) {
                $logicalOperator = strtoupper($condition);
                continue;
            }

            if (str_starts_with($condition, '(') && str_ends_with($condition, ')')) {
                $groupFilters = $this->customFilterParser(substr($condition, 1, -1));

                $filters[] = [
                    'group' => $groupFilters,
                    'logical_operator' => $logicalOperator,
                ];
            } else {
                $parsedCondition = $this->parseCondition($condition);

                if ($parsedCondition) {
                    $parsedCondition['logical_operator'] = $logicalOperator;
                    $filters[] = $parsedCondition;
                }
            }

            $logicalOperator = null;
        }

        return $filters;
    }

    private function splitConditions(string $filterString): array
    {
        $conditions = [];
        $depth = 0;
        $start = 0;
        $length = strlen($filterString);
        $logicalOperators = ['AND', 'OR'];

        for ($i = 0; $i < $length; $i++) {
            $char = $filterString[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($char === ' ' && $depth === 0) {
                $nextChars = substr($filterString, $i + 1, 3); // 2 for OR, 3 for AND
                $nextChars = trim($nextChars); // so get 3 and trim anyway

                if (in_array($nextChars, $logicalOperators)) {
                    $condition = substr($filterString, $start, $i - $start);
                    $conditions[] = trim($condition);
                    $conditions[] = trim($nextChars);

                    $i += strlen($nextChars);
                    $start = $i + 1;
                }
            }
        }

        if ($start < $length) {
            $conditions[] = trim(substr($filterString, $start));
        }

        return $conditions;
    }

    private function parseCondition(string $condition): ?array
    {
        $operators = ['>=', '<=', '!=', '=', '>', '<', 'LIKE', 'IN', 'HAS_ANY', 'IS_ANY', 'EXISTS'];

        foreach ($operators as $operator) {
            if (str_contains($condition, $operator)) {
                [$field, $value] = explode($operator, $condition, 2);
                $field = trim($field);
                $value = trim($value);

                if (in_array($operator, ['IN', 'HAS_ANY', 'IS_ANY'])) {
                    $value = array_map('trim', explode(',', trim($value, '()')));
                }

                return [
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value,
                ];
            }
        }

        return null;
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $eagerLoad = [];
        $eagerLoadConditions = [];

        foreach ($filters as $filter) {
            if (isset($filter['group'])) {
                $query->where(fn($subQuery) => $this->applyFilters($subQuery, $filter['group']));
            } else {
                $this->applyCondition($query, $filter, $eagerLoad, $eagerLoadConditions);
            }
        }

        $this->applyEagerLoadConditions($query, $eagerLoad, $eagerLoadConditions);
    }

    private function applyCondition(Builder $query, array $filter, array &$eagerLoad, array &$eagerLoadConditions): void
    {
        $field = $filter['field'];
        $operator = $filter['operator'];
        $value = $filter['value'];

        $logicalOperator = strtoupper($filter['logical_operator']);

        if (in_array($field, ['languages', 'locations', 'categories'])) {
            $this->handleEagerLoadCondition($field, $operator, $value, $logicalOperator, $eagerLoad, $eagerLoadConditions);
        } elseif (str_starts_with($field, 'attribute:')) {
            $this->handleAttributeCondition($query, $field, $operator, $value, $logicalOperator);
        } else {
            $this->applyBasicCondition($query, $field, $operator, $value, $logicalOperator);
        }
    }

    private function handleEagerLoadCondition(string $relation, string $operator, $value, string $logicalOperator, array &$eagerLoad, array &$eagerLoadConditions): void
    {
        $eagerLoad[] = $relation;
        $eagerLoadConditions[$relation][] = [
            'field' => $relation === 'locations' ? 'city' : 'name',
            'operator' => $operator,
            'value' => $value,
            'logical_operator' => $logicalOperator,
        ];
    }

    private function handleAttributeCondition(Builder $query, string $field, string $operator, $value, string $logicalOperator): void
    {
        $attributeKey = substr($field, 10);

        $query->join('attribute_job', 'jobs.id', '=', 'attribute_job.job_id')
              ->join('attributes', 'attribute_job.attribute_id', '=', 'attributes.id')
              ->where('attributes.name', $attributeKey);

        $this->applyBasicCondition($query, 'attribute_job.value', $operator, $value, $logicalOperator);
    }

    private function applyBasicCondition(Builder $query, string $field, string $operator, $value, string $logicalOperator): void
    {
        $method = $logicalOperator === 'OR' ? 'orWhere' : 'where';

        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '<':
            case '>=':
            case '<=':
            case 'LIKE':
                $query->$method($field, $operator, $operator === 'LIKE' ? "%$value%" : $value);
                break;
            case 'IN':
            case 'HAS_ANY':
            case 'IS_ANY':
                $query->{$method . 'In'}($field, $value);
                break;
            case 'EXISTS':
                $query->{$method . 'Exists'}(fn($subQuery) => $subQuery->select(DB::raw(1))->from($field));
                break;
            default:
                throw new Exception("Unsupported operator: $operator");
        }
    }

    private function applyEagerLoadConditions(Builder $query, array $eagerLoad, array $eagerLoadConditions): void
    {
        if (!empty($eagerLoad)) {
            $query->with($eagerLoad);

            foreach ($eagerLoadConditions as $relation => $conditions) {
                $query->whereHas($relation, fn($subQuery) => $this->applyConditionsToSubQuery($subQuery, $conditions));
            }
        }
    }

    private function applyConditionsToSubQuery(Builder $subQuery, array $conditions): void
    {
        foreach ($conditions as $condition) {
            $this->applyBasicCondition($subQuery, $condition['field'], $condition['operator'], $condition['value'], $condition['logical_operator']);
        }
    }
}
