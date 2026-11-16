<?php

namespace App\Http\Requests;

use App\Models\VisitorCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class StoreVisitorInvitationBatchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $emailArray = $this->input('recipient_emails');
        if (is_array($emailArray)) {
            $emails = array_map(fn ($email) => is_string($email) ? trim($email) : $email, $emailArray);
        } else {
            $emailInput = $this->input('recipient_emails_input', '');
            $emails = is_string($emailInput)
                ? (preg_split('/[\r\n,;]+/', $emailInput) ?: [])
                : [];
        }

        $this->merge([
            'recipient_emails' => array_values(array_filter(
                array_map(fn ($email) => is_string($email) ? trim($email) : $email, $emails),
                fn ($email) => $email !== ''
            )),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
            'host_employee_id' => ['required', Rule::exists('employees', 'id')->where('is_active', true)],
            'visitor_category_id' => ['required', Rule::exists('visitor_categories', 'id')->where('is_active', true)],
            'visitor_purpose_id' => ['required', Rule::exists('visitor_purposes', 'id')->where('is_active', true)],
            'visit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'expected_arrival_time' => ['required', 'date_format:H:i'],
            'expected_departure_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:visit_date'],
            'expected_departure_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:visit_date'],
            'share_link_count' => ['required', 'integer', 'min:0', 'max:100'],
            'recipient_emails_input' => ['nullable', 'string', 'max:20000'],
            'recipient_emails' => ['array', 'max:100'],
            'recipient_emails.*' => ['email', 'max:255', 'distinct:ignore_case'],
        ];
    }

    public function withValidator(ValidationValidator $validator): void
    {
        $validator->after(function (ValidationValidator $validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $emailCount = count($this->input('recipient_emails', []));
            $linkCount = (int) $this->input('share_link_count') + $emailCount;
            if ($linkCount < 1) {
                $validator->errors()->add('share_link_count', 'Create at least one link or enter recipient email addresses.');
                return;
            }
            if ($linkCount > 100) {
                $validator->errors()->add('share_link_count', 'A batch can contain at most 100 invitation links.');
                return;
            }

            $user = $this->user();
            $isAdmin = $user->hasRole('admin') || $user->roles()->where('roles.id', 1)->exists();
            $branchId = $user->employee?->employment?->branch_id;
            if (!$isAdmin && (int) $branchId !== (int) $this->input('branch_id')) {
                $validator->errors()->add('branch_id', 'You are not allowed to create invitations for this branch.');
            }

            $category = VisitorCategory::find($this->input('visitor_category_id'));
            if ($category->requires_additional_info && empty($this->input('notes'))) {
                $validator->errors()->add('notes', 'Notes are required for this category.');
            }

            $visitDate = $this->input('visit_date');
            $arrival = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $visitDate . ' ' . $this->input('expected_arrival_time')
            );
            $departureDate = $this->input('expected_departure_date') ?: $visitDate;
            $departure = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $departureDate . ' ' . $this->input('expected_departure_time')
            );
            if ($departure->lessThanOrEqualTo($arrival)) {
                $validator->errors()->add('expected_departure_time', 'Expected departure must be after expected arrival.');
            }
        });
    }
}
