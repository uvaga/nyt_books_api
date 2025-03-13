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
            'author' => 'nullable|string',
            'isbn' => ['nullable', 'string', 'regex:/^(\d{10}|\d{13})(;\d{10}|\d{13})*$/'],
            'title' => 'nullable|string',
            'offset' => 'nullable|integer|min:0',
        ];
    }
}
