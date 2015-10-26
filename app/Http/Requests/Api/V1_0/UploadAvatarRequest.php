<?php

namespace App\Http\Requests\Api\V1_0;

use App\Http\Requests\JsonRequest;

class UploadAvatarRequest extends JsonRequest
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
            'avatar' => 'required',
            'skip_profile' => 'sometimes|required'
        ];
    }
}