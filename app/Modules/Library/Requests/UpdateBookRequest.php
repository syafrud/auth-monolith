<?php

namespace Modules\Library\Requests;

use Illuminate\Validation\Rule;
use Modules\Library\Rules\Isbn;

class UpdateBookRequest extends StoreBookRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'isbn' => ['bail', 'required', 'string', new Isbn, Rule::unique('books', 'isbn')->ignore($this->route('book'))],
        ];
    }
}
