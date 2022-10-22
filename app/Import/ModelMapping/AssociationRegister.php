<?php

namespace App\Import\ModelMapping;

use Closure;
use hanneskod\classtools\Transformer\Reader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * <p>This singleton register is the place to register callbacks which will be triggered as soon as the saving of all
 * models of one row has been successfully ended. Your callback will be automatically invoked in case all models have
 * been found which are the callback's parameters.
 * </p>
 * <p>
 * <i>The parameters of the callback are up to you, as long as they all extend the {@link Model}. In case your callback
 * has not been called, at least one model could not been identified beforehand.</i>
 * </p>
 * <p>
 * Therefore, make sure your {@link IdentificationOf identifications}
 * and the corresponding {@link IdentificationOf::propertyMapping()} is working properly.
 * </p>
 */
class AssociationRegister
{
    private Collection $closures;

    public function __construct()
    {
        $this->closures = collect();
    }

    /**
     * You can register hooks here for associating models with each other or in case you want to know if there has
     * been $this model found and also another one.
     * For example, registration is possible:<br>
     * <code>
     * registerClosure(fn (self $modelA, Foo $foo) => $modelA->associate($foo)->save())
     * </code>
     *
     * @param Closure $closure
     *
     * @return self
     */
    public function registerClosure(Closure $closure): self
    {
        $this->closures->push($closure);

        return $this;
    }

    public function registerAssociationOf(AssociationOf $associationOf): self
    {
        $this->closures = $this->closures->merge($associationOf->associationOfClosures());

        return $this;
    }

    /**
     * @return Collection<IdentificationOf>
     */
    public function getClosures(): Collection
    {
        return $this->closures;
    }
}
