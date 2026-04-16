<?php

namespace App\Http\Requests;

use App\Models\ContentSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $pageType  = $this->input('page_type', $this->route('content_setting')?->page_type);
        $isAllPage = $pageType === ContentSetting::PAGE_ALL;

        return [
            'page_type'    => ['sometimes', 'in:' . implode(',', ContentSetting::PAGE_TYPES)],
            'icon'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'icon_url'     => ['nullable', 'string', 'max:500'],
            'title'        => [$isAllPage ? 'required' : 'nullable', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string', 'max:255'],
            'text_content' => ['nullable', 'string', 'max:2000'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'status'       => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'page_type.in'   => 'Invalid page type selected.',
            'title.required' => 'Title is required for the All Page section.',
        ];
    }
}
