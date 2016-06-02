<?php

namespace App\Http\Requests\Api\V1_0;

use App\Http\Requests\AbstractJsonRequest;
use App\Project;
use Gate;

class UpdateProjectRequest extends AbstractJsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $projectId = $this->route('id');
        $project = Project::findOrFail($projectId);

        return Gate::allows('update', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data.type' => 'required|in:projects',
            'data.attributes' => 'required',
            'data.attributes.name' => 'string',
            'data.attributes.is_published' => 'boolean',
            'data.attributes.permission' => 'numeric',
        ];
    }
}