<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $wallet = $this->route('wallet');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('wallets', 'name')->ignore($wallet?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama dompet wajib diisi.',
            'name.unique' => 'Nama dompet sudah dipakai. Gunakan nama lain.',
            'name.max' => 'Nama dompet maksimal 100 karakter.',
        ];
    }
}