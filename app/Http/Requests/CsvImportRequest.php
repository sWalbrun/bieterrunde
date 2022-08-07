<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * This request gets used to import some models via an attached xlsx or csv file.
 */
class CsvImportRequest extends FormRequest
{
    public const FILE = 'FILE';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            self::FILE => 'required|file',
        ];
    }
}
