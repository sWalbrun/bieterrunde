<?php

namespace App\Import;

use App\Import\ModelMapping\ModelMapping;

/**
 * A simple data class for scoping {@link ModelMapping} with {@link ColumnMapping::$column model columns} of
 * {@link ModelMapping::$model}.
 */
class ColumnMapping
{
    public ModelMapping $mapping;

    public string $column;

    public string $originalRegEx;

    public function __construct(ModelMapping $parent, string $column, string $originalRegEx)
    {
        $this->mapping = $parent;
        $this->column = $column;
        $this->originalRegEx = $originalRegEx;
    }
}
