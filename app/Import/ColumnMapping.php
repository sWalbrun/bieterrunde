<?php

namespace App\Import;

use App\Import\ModelMapping\IdentificationOf;

/**
 * A simple data class for scoping {@link IdentificationOf} with {@link ColumnMapping::$column model columns} of
 * {@link IdentificationOf::$model}.
 */
class ColumnMapping
{
    public IdentificationOf $identificationOf;

    public string $column;

    public string $originalRegEx;

    public function __construct(IdentificationOf $identificationOf, string $column, string $originalRegEx)
    {
        $this->identificationOf = $identificationOf;
        $this->column = $column;
        $this->originalRegEx = $originalRegEx;
    }
}
