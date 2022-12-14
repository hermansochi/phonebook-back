<?php

namespace App\Http\Requests\Github;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexContributorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page' => 'integer',
            'per_page' => 'integer',
            'sort' => Rule::in(['login', '-login', 'contributions', '-contributions']),
        ];
    }
}
