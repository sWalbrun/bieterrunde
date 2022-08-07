<?php

namespace App\Import\ModelMapping;

use Illuminate\Support\Collection;

/**
 * Use this register to register your {@link ModelMapping} (one mapping per model).
 */
class MappingRegister
{
    private Collection $mappings;

    public function __construct(Collection $mappings)
    {
        $this->mappings = $mappings;
    }

    public function register(ModelMapping $mapping): self
    {
        if (!$this->mappings->contains(fn (ModelMapping $existingMapping) => $existingMapping === $mapping)) {
            $this->mappings->push($mapping);
        }

        return $this;
    }

    /**
     * @return Collection<ModelMapping>
     */
    public function getMappings(): Collection
    {
        return $this->mappings;
    }
}
