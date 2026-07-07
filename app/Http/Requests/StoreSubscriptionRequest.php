<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates POST /subscriptions (docs/API-CONTRACT.md §4). Public + honeypot:
 * the `website` field must stay empty. An SMS contact is normalized as a phone;
 * an email contact must be a valid address.
 */
class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('channel') === 'sms' && is_string($this->input('contact'))) {
            $this->merge(['contact' => PhoneNumber::normalize($this->input('contact'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'in:email,sms,telegram'],
            'region' => ['required', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            // Honeypot: legitimate clients never send this field.
            'website' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('contact', 'email', fn (): bool => $this->input('channel') === 'email');
    }
}
