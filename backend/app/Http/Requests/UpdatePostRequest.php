<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:news,article',
            'cover_image_url' => 'nullable|url',
            'status' => 'sometimes|required|in:draft,pending,approved,rejected,archived',
        ];
    }
}
