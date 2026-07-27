<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string'],
            'video_url'      => ['nullable', 'url'],
            'valuation'      => ['required', 'numeric', 'min:0'],
            'investment_url' => ['nullable', 'url'],
            'date'           => ['nullable', 'date'],
            
            // Opsional jika membawa relasi array saat pembuatan
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
        ];
    }
}