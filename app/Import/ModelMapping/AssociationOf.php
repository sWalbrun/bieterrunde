<?php

namespace App\Import\ModelMapping;

use Illuminate\Support\Collection;

interface AssociationOf
{
    /**
     * @return Collection<AssociationOf>
     */
    public function associationOfClosures(): Collection;
}
