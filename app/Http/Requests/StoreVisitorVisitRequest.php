<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\VisitorCategory;

class StoreVisitorVisitRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $visitDate = $this->input('visit_date');
        if (!is_string($visitDate)) {
            return;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $visitDate);
        $errors = \DateTimeImmutable::getLastErrors();
        if (
            !$date
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $visitDate
        ) {
            return;
        }

        $formattedDate = $date->format('d F Y');
        $normalized = [
            'expected_arrival_date' => $this->filled('expected_arrival_date')
                ? $this->input('expected_arrival_date')
                : $formattedDate,
        ];

        if (!$this->filled('expected_departure_date')) {
            $normalized['expected_departure_date'] = $formattedDate;
        } elseif (is_string($this->input('expected_departure_date'))) {
            $departureDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->input('expected_departure_date'));
            $departureErrors = \DateTimeImmutable::getLastErrors();
            if (
                $departureDate
                && ($departureErrors === false || ($departureErrors['warning_count'] === 0 && $departureErrors['error_count'] === 0))
                && $departureDate->format('Y-m-d') === $this->input('expected_departure_date')
            ) {
                $normalized['expected_departure_date'] = $departureDate->format('d F Y');
            }
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'company' => ['nullable', 'string', 'max:255'],
            'visitor_category_id' => ['required', Rule::exists('visitor_categories', 'id')->where('is_active', true)],
            'visitor_purpose_id' => ['required', Rule::exists('visitor_purposes', 'id')->where('is_active', true)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
            'host_employee_id' => ['required', Rule::exists('employees', 'id')->where('is_active', true)],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'expected_arrival_date' => ['required', 'date_format:d F Y'],
            'expected_arrival_time' => ['required', 'date_format:H:i'],
            'expected_departure_date' => ['required', 'date_format:d F Y'],
            'expected_departure_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $category = VisitorCategory::find($this->input('visitor_category_id'));
            if ($category->requires_additional_info && empty($this->input('notes'))) {
                $validator->errors()->add('notes', 'Notes are required for this category.');
                return;
            }

            $visitDate = \Carbon\Carbon::parse($this->input('visit_date'))->toDateString();
            $arrival = \Carbon\Carbon::createFromFormat(
                '!d F Y H:i',
                $this->input('expected_arrival_date') . ' ' . $this->input('expected_arrival_time')
            );
            $departure = \Carbon\Carbon::createFromFormat(
                '!d F Y H:i',
                $this->input('expected_departure_date') . ' ' . $this->input('expected_departure_time')
            );

            $arrivalDate = \Carbon\Carbon::createFromFormat('!d F Y', $this->input('expected_arrival_date'))->toDateString();
            $departureDate = \Carbon\Carbon::createFromFormat('!d F Y', $this->input('expected_departure_date'))->toDateString();

            if ($arrivalDate !== $visitDate) {
                $validator->errors()->add('expected_arrival_date', 'Expected arrival must be on the visit date.');
            }
            if ($departureDate < $visitDate) {
                $validator->errors()->add('expected_departure_date', 'Expected departure cannot be before the visit date.');
            }

            if ($departure->lessThanOrEqualTo($arrival)) {
                $validator->errors()->add('expected_departure_time', 'Expected departure must be after expected arrival.');
            }
        });
    }
}
