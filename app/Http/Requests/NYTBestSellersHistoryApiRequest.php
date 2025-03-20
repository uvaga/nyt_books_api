<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NYTBestSellersHistoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author' => ['sometimes', 'string', 'max:255'],
            'isbn' => ['sometimes' , 'array'],
            'isbn.*' => ['sometimes', 'string', 'regex:/^(\d{10}|\d{13})$/'],
            'title' => ['sometimes', 'string', 'max:400'],
            'offset' => ['sometimes', 'integer', 'min:0', 'multiple_of:20'],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('isbn')) {
            $this->merge([
                'isbn' => explode(';', $this->input('isbn')),
            ]);
        }
    }

}
