<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization handled in controller
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:news,article',
            'cover_image_url' => 'nullable|url',
            'status' => 'required|in:draft,pending,approved,rejected,archived',
        ];
    }
}
