<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'data' => 'required|array',
            'data.type' => 'required|in:groups',
            'data.attributes' => 'required|array',
            'data.attributes.title' => 'required|string|unique:groups,title',
            'data.attributes.project_id' => 'required|integer',
            'data.attributes.user_id' => 'required|integer',
        ];
    }
}
