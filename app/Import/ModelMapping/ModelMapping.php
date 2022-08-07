<?php

namespace App\Import\ModelMapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * You have to extend this mapping to make it possible to import a {@link __construct() model} via the import.
 */
abstract class ModelMapping
{
    protected bool $associate;
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
     * @return array
     */
    abstract public function uniqueColumns(): array;

    /**
     * You can register hooks here for associating models with each other or in case you want to know if there has
     * been $this model found and also another one.
     * For example, registration is possible:<br>
     * <code>
     * return [
     *     Foo::class => fn (self $modelA, Foo $foo) => $modelA->associate($foo)->save()
     *   ]
     * </code>
     * The key is the model which has to be found (additional to $this model) to trigger the value's callback.
     * @return array
     */
    public function associationHooks(): array
    {
        return [];
    }
}
