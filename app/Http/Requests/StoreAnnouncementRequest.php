<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $roleNames = $user->roles->pluck('name')->all();
        $roleIds = $user->roles->pluck('id')->all();

        return in_array('hr', $roleNames, true)
            || in_array('management', $roleNames, true)
            || in_array('admin', $roleNames, true)
            || in_array(1, $roleIds, true)
            || in_array(3, $roleIds, true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'all_employees' => $this->boolean('all_employees'),
            'send_email' => $this->boolean('send_email'),
            'send_push_notification' => $this->boolean('send_push_notification'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:announcement_categories,id'],
            'link' => ['nullable', 'url'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'content' => ['required', 'string'],
            'all_employees' => ['required', 'boolean'],
            'send_email' => ['sometimes', 'boolean'],
            'send_push_notification' => ['sometimes', 'boolean'],
            'branches' => ['sometimes', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'organizations' => ['sometimes', 'array'],
            'organizations.*' => ['integer', 'exists:organizations,id'],
            'job_levels' => ['sometimes', 'array'],
            'job_levels.*' => ['integer', 'exists:job_levels,id'],
            'positions' => ['sometimes', 'array'],
            'positions.*' => ['integer', 'exists:positions,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('all_employees')) {
                return;
            }

            $hasAudience = collect([
                'branches',
                'organizations',
                'job_levels',
                'positions',
            ])->contains(function (string $field) {
                return !empty(array_filter((array) $this->input($field, [])));
            });

            if (!$hasAudience) {
                $validator->errors()->add(
                    'custom_audience',
                    'Select at least one branch, organization, job level, or position when Custom Audience is selected.'
                );
            }
        });
    }
}
