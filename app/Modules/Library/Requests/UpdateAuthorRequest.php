<?php

namespace Modules\Library\Requests;

use Illuminate\Validation\Rule;

class UpdateAuthorRequest extends StoreAuthorRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['required', 'email', 'max:255', Rule::unique('authors', 'email')->ignore($this->route('author'))],
        ];
    }
}
