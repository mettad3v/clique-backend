<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
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
            'data.type' => 'required|in:tasks',
            'data.attributes' => 'required|array',
            'data.attributes.title' => 'required|string|unique:tasks,title',
            'data.attributes.description' => 'string',
            'data.attributes.deadline' => 'date_format:Y-m-d H:i:s',
        ];
    }
}
