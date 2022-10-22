<?php

namespace App\Import\ModelMapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * You have to extend this mapping to make it possible to import a {@link __construct() model} via the import.
 * Furthermore, make sure your {@link Model::$fillable model's attributes are fillable}.
 */
abstract class IdentificationOf
{
    public Model $model;

    /**
     * @param Model $model This mapping is referring to this model.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * This method returns the mapping between the columns of the model and the regEx for determining the column
     * from the Excel file. The key is the colunmn and the value is the mapping.
     * <b> Make sure you do not forget the delimiters. <u>/</u>regEx<u>/</u></b>
     *
     * @return Collection<string, string>
     */
    abstract public function propertyMapping(): Collection;

    /**
     * In case the model is having unique columns (combined), those get used for fetching existing models.
     *
     * @return array
     */
    abstract public function uniqueColumns(): array;

    /**
     * You can overwrite this hook in case you want to make some manipulations before the model gets saved.
     *
     * @param Model $model
     *
     * @return void
     */
    public function saving(Model $model): void
    {
    }

    /**
     * You can overwrite this hook in case you want to make some manipulations before the model gets saved.
     *
     * @param Model $model
     *
     * @return void
     */
    public function saved(Model $model): void
    {
    }
}
