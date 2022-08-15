<?php

namespace App\Import;

use App\Import\ModelMapping\MappingRegister;
use App\Import\ModelMapping\ModelMapping;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

/**
 * This processor is trying to create models using the {@link ModelMapping::propertyMapping()} taking care of
 * {@link ModelMapping::uniqueColumns() unique columns} making sure the import can be idempotent.<br>
 * Associations get also set in case {@link ModelMapping::associationHooks() hooks} are set.
 */
class ImportProcessor implements OnEachRow
{
    private MappingRegister $register;

    private bool $firstRow = true;

    private Collection $nonMatchingHeadingCells;

    /**
     * @var Collection<string, ColumnMapping>
     */
    private Collection $headingToColumnMapping;

    public function __construct(MappingRegister $register)
    {
        $this->register = $register;
        $this->nonMatchingHeadingCells = collect();
        $this->headingToColumnMapping = collect();
    }

    /**
     * @param Row $row
     *
     * @return void
     *
     * @throws Exception
     */
    public function onRow(Row $row)
    {
        $collectedRow = $row->toCollection();
        if ($this->firstRow) {
            $this->determineHeader($collectedRow);
            $this->firstRow = false;

            return;
        }
        $this->handleDataRow($collectedRow);
    }

    /**
     * @param Collection $row
     *
     * @return void
     *
     * @throws Exception
     */
    private function determineHeader(Collection $row): void
    {
        $row->each(function (?string $cell, int $index) {
            if (!isset($cell)) {
                // Since the cell is not having a heading row, we cannot process it since it gets used as regEx subject
                return;
            }
            $this->register
                ->getMappings()
                ->each(function (ModelMapping $mapping) use ($index, $cell) {
                    $matchingColumns = $mapping->propertyMapping()
                        ->filter(fn (string $regEx, string $column) => preg_match($regEx, $cell) || $column === $cell);
                    if ($matchingColumns->count() > 1) {
                        // The same table is having some overlapping regular expressions
                        $this->throwOverlappingException($matchingColumns->implode(', '), $cell);
                    }
                    if ($matchingColumns->isEmpty()) {
                        return;
                    }

                    if ($this->headingToColumnMapping->has($index)) {
                        // Several tables have overlapping regular expressions
                        // phpcs:ignore
                        /** @var ColumnMapping $mapping */
                        $mapping = $this->headingToColumnMapping->get($index);
                        $this->throwOverlappingException(collect([
                            $mapping->originalRegEx,
                            $matchingColumns->first(),
                        ])->implode(', '), $cell);
                    }
                    $this->headingToColumnMapping->put(
                        $index,
                        new ColumnMapping($mapping, $matchingColumns->keys()->first(), $matchingColumns->first())
                    );
                });
            if (!$this->headingToColumnMapping->has($index)) {
                $this->nonMatchingHeadingCells->push($cell);
            }
        });
    }

    private function handleDataRow(Collection $row): void
    {
        $this->reset();
        $this->setAttributes($row);
        $this->persistAllModels();
        $this->setRelations();
    }

    private function reset()
    {
        $this->headingToColumnMapping->each(
            fn (ColumnMapping $columnValue) => $columnValue->mapping->model = $columnValue->mapping->model->newInstance()
        );
    }

    private function setAttributes(Collection $row): void
    {
        $row->each(function (?string $cell, int $index) {
            // phpcs:ignore
            /** @var ColumnMapping $columnValue */
            $columnValue = $this->headingToColumnMapping->get($index);
            if (!isset($columnValue)) {
                // This column has not been recognized. We can just skip it.
                return;
            }

            $columnValue->mapping->model->{$columnValue->column} = $cell;
        });
    }

    private function setRelations()
    {
        $allModels = $this->headingToColumnMapping->map(fn (ColumnMapping $columnValue) => $columnValue->mapping->model);
        $this->headingToColumnMapping->map(function (ColumnMapping $columnValue) {
            return $columnValue->mapping;
        })->unique()
        ->each(function (ModelMapping $mapping) use ($allModels) {
            collect($mapping->associationHooks())
                ->each(function (Closure $callback, string $relatedClass) use ($mapping, $allModels) {
                    $modelToRelate = $allModels->first(fn (Model $model) => get_class($model) === $relatedClass);
                    if (!isset($modelToRelate)) {
                        return;
                    }
                    $callback($mapping->model, $modelToRelate);
                });
        });
    }

    private function persistAllModels(): void
    {
        $this->headingToColumnMapping
            ->unique(fn (ColumnMapping $columnValue) => $columnValue->mapping)
            ->each(function (ColumnMapping $columnMapping) {
                if (count($columnMapping->mapping->uniqueColumns()) >= 0) {
                    $model = $columnMapping->mapping->model;
                    $uniqueColumns = collect($model->getAttributes())->filter(
                        fn ($value, string $column) => in_array($column, $columnMapping->mapping->uniqueColumns())
                    );

                    $builder = $model->newQuery();
                    $uniqueColumns->each(fn ($value, string $column) => $builder->where($column, '=', $value));
                    $modelToPersist = $builder->firstOrNew();
                    $modelToPersist->fill($model->getAttributes());
                    $columnMapping->mapping->preSaveHook($modelToPersist);
                    $modelToPersist->save();
                    $columnMapping->mapping->model = $modelToPersist;

                    return;
                }
                $columnMapping->mapping->model->save();
            });
    }

    /**
     * @param string $implode
     * @param string $cell
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function throwOverlappingException(string $implode, string $cell)
    {
        throw new Exception(
            'The regex\'s result is overlapping. More than one matching regex ('
            . $implode
            . ") has been found for column ($cell)"
        );
    }
}
