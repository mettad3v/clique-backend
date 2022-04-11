<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'data.id' => 'required|string',
            'data.type' => 'required|in:users',
            'data.attributes' => 'required|array',
            'data.attributes.name' => 'sometimes|string',
            'data.attributes.profile_avatar' => 'sometimes|image|mimes:jpg,png,jpeg,gif,svg',
            'data.attributes.email' => 'sometimes|email|unique:users,email',
            'data.attributes.username' => 'sometimes|string|unique:users,username',
            'data.attributes.status' => 'sometimes|boolean',
        ];
    }
}
