<?php

namespace App\Support\PowerGrid;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Schema;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\DataSource\Processors\Database\Handlers\SearchHandlerContract;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Support\PowerGridTableCache;
use stdClass;
use Throwable;

class CaseInsensitiveSearchHandler implements SearchHandlerContract
{
    public function __construct(
        protected readonly PowerGridComponent $component
    ) {}

    public function apply(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        if (blank($this->component->search)) {
            return $query;
        }

        $search = trim(strtolower(htmlspecialchars($this->component->search, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $hasRelationSearch = count($this->component->relationSearch()) && $query instanceof EloquentBuilder;

        $query->where(function (EloquentBuilder|QueryBuilder $subQuery) use ($search, $hasRelationSearch) {
            $modelTable = $subQuery instanceof QueryBuilder ? $subQuery->from : $subQuery->getModel()->getTable();
            $columnList = $this->getColumnList($subQuery, $modelTable);

            collect($this->component->columns)
                ->filter(fn(stdClass|array|Column $column) => (bool) data_get($column, 'searchable'))
                ->each(function (stdClass|array|Column $column) use ($subQuery, $search, $columnList, $hasRelationSearch) {
                    $field = $this->getDataField($column);
                    [$table, $field] = $this->splitField($subQuery, $field);
                    $fieldSearch = $this->getBeforeSearchMethod($field, $search);

                    if (blank($fieldSearch)) {
                        return;
                    }

                    if (empty($table)) {
                        $this->orWhereLowerLike($subQuery, $field, $fieldSearch);

                        return;
                    }

                    if (isset($columnList[$field]) || ! $hasRelationSearch) {
                        $this->orWhereLowerLike($subQuery, "{$table}.{$field}", $fieldSearch);
                    }
                });

            if ($hasRelationSearch && $subQuery instanceof EloquentBuilder) {
                $this->filterRelation($subQuery, $search);
            }
        });

        return $query;
    }

    protected function filterRelation(EloquentBuilder $query, string $search): void
    {
        foreach ($this->component->relationSearch() as $table => $columns) {
            if (is_array($columns)) {
                $this->filterNestedRelation($query, $table, $columns, $search);

                continue;
            }

            $query->orWhereHas($table, function (EloquentBuilder $subQuery) use ($columns, $search) {
                $fieldSearch = $this->getBeforeSearchMethod($columns, $search);
                $subQuery->when($fieldSearch, fn(EloquentBuilder $q) => $this->whereLowerLike($q, $columns, $fieldSearch));
            });
        }
    }

    protected function filterNestedRelation(EloquentBuilder $query, string $table, array $columns, string $search): void
    {
        foreach ($columns as $nestedTable => $nestedColumns) {
            if (is_array($nestedColumns)) {
                $nestedTableWithDot = $table.'.'.$nestedTable;
                $query->orWhereHas($nestedTableWithDot, function (EloquentBuilder $subQuery) use ($nestedColumns, $search) {
                    $this->whereAnyLowerLike($subQuery, $nestedColumns, $search);
                });

                continue;
            }

            $query->orWhereHas($table, function (EloquentBuilder $subQuery) use ($nestedColumns, $search) {
                $fieldSearch = $this->getBeforeSearchMethod($nestedColumns, $search);
                $subQuery->when($fieldSearch, fn(EloquentBuilder $q) => $this->whereLowerLike($q, $nestedColumns, $fieldSearch));
            });
        }
    }

    protected function whereAnyLowerLike(EloquentBuilder $query, array $columns, string $search): void
    {
        $query->where(function (EloquentBuilder $subQuery) use ($columns, $search) {
            foreach ($columns as $column) {
                $fieldSearch = $this->getBeforeSearchMethod($column, $search);

                if (filled($fieldSearch)) {
                    $this->orWhereLowerLike($subQuery, $column, $fieldSearch);
                }
            }
        });
    }

    protected function whereLowerLike(EloquentBuilder|QueryBuilder $query, string $column, string $search): void
    {
        $query->whereRaw('LOWER('.$this->wrapColumn($query, $column).') LIKE ?', ['%'.$search.'%']);
    }

    protected function orWhereLowerLike(EloquentBuilder|QueryBuilder $query, string $column, string $search): void
    {
        $query->orWhereRaw('LOWER('.$this->wrapColumn($query, $column).') LIKE ?', ['%'.$search.'%']);
    }

    protected function wrapColumn(EloquentBuilder|QueryBuilder $query, string $column): string
    {
        return $query->getQuery()->getGrammar()->wrap($column);
    }

    protected function getColumnList(EloquentBuilder|QueryBuilder $query, string $modelTable): array
    {
        $connection = $query instanceof EloquentBuilder
            ? $query->getModel()->getConnection()->getName()
            : $query->getConnection()->getConfig('name');

        try {
            return PowerGridTableCache::getOrCreate(
                $modelTable,
                fn() => collect(Schema::connection($connection)->getColumns($modelTable))
                    ->pluck('type', 'name')
                    ->toArray()
            );
        } catch (Throwable) {
            return Schema::connection($connection)->getColumnListing($modelTable);
        }
    }

    protected function getDataField(Column|stdClass|array $column): string
    {
        return strval(data_get($column, 'dataField')) ?: strval(data_get($column, 'field'));
    }

    protected function getBeforeSearchMethod(string $field, ?string $search): ?string
    {
        $method = 'beforeSearch'.str($field)->headline()->replace(' ', '');

        if (method_exists($this->component, $method)) {
            return $this->component->$method($search);
        }

        if (method_exists($this->component, 'beforeSearch')) {
            return $this->component->beforeSearch($field, $search);
        }

        return $search;
    }

    protected function splitField(EloquentBuilder|QueryBuilder $query, string $field): array
    {
        $table = $query instanceof QueryBuilder ? $query->from : $query->getModel()->getTable();

        if (str_contains($field, '.')) {
            $explodeField = explode('.', $field);
            $table = $explodeField[0];
            $field = $explodeField[1];
        }

        return [$table, $field];
    }
}
