<?php

namespace App\Http\Livewire;

use App\Enums\VegetableType;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Vegetable extends Component
{

    public string $name;
    public VegetableType $unit;

    public function submit()
    {
        $validatedData = $this->validate(
            [
                'name' => 'required|string',
                'unit' => ['required', Rule::in(VegetableType::asArray())],
            ]
        );

        \App\Models\Vegetable::create($validatedData);

        return redirect()->to('/form');
    }

    public function render()
    {
        return view('livewire.vegetable');
    }
}
