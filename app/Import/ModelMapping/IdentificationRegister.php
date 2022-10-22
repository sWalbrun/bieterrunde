<?php

namespace App\Import\ModelMapping;

use Illuminate\Support\Collection;

/**
 * Use this register to register your {@link IdentificationOf} (one mapping per model).
 */
class IdentificationRegister
{
    private Collection $mappings;

    public function __construct(Collection $mappings)
    {
        $this->mappings = $mappings;
    }

    public function register(IdentificationOf $mapping): self
    {
        if (!$this->mappings->contains(fn (IdentificationOf $existingMapping) => $existingMapping === $mapping)) {
            $this->mappings->push($mapping);
        }

        return $this;
    }

    /**
     * @return Collection<IdentificationOf>
     */
    public function getMappings(): Collection
    {
        return $this->mappings;
    }
}
