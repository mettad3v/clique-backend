<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'data.type' => 'required|in:tasks',
            'data.attributes' => 'required|array',
            'data.attributes.title' => 'sometimes|required|string|unique:tasks,title',
            'data.attributes.description' => 'sometimes|string',
            'data.attributes.user_id' => 'sometimes|required|integer',
            'data.attributes.project_id' => 'sometimes|required|integer',
            'data.attributes.category_id' => 'sometimes|required|integer',
            'data.attributes.deadline' => 'sometimes|date_format:Y-m-d H:i:s',
        ];
    }
}
