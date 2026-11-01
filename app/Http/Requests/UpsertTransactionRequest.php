<?php

namespace App\Http\Requests;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'integer', 'min:1'],
            'wallet_id' => ['required', 'integer', 'exists:wallets,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id', $this->categoryMustMatchType()],
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['required', 'date_format:H:i,H:i:s'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Jenis transaksi wajib sama dengan jenis kategorinya.
     */
    private function categoryMustMatchType(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $category = Category::query()->find($value);

            if ($category !== null && $category->type->value !== $this->input('type')) {
                $fail('Kategori yang dipilih tidak sesuai dengan jenis transaksi.');
            }
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'type.in' => 'Jenis transaksi tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.integer' => 'Nominal harus berupa angka bulat tanpa desimal.',
            'amount.min' => 'Nominal harus lebih dari 0.',
            'wallet_id.required' => 'Dompet wajib dipilih.',
            'wallet_id.exists' => 'Dompet yang dipilih tidak valid.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'transaction_date.required' => 'Tanggal wajib diisi.',
            'transaction_date.date' => 'Tanggal tidak valid.',
            'transaction_time.required' => 'Jam wajib diisi.',
            'transaction_time.date_format' => 'Jam tidak valid (format HH:MM).',
            'notes.max' => 'Catatan terlalu panjang (maksimal 2000 karakter).',
        ];
    }
}