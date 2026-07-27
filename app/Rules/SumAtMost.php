<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Memvalidasi bahwa JUMLAH sebuah kolom numerik di seluruh item array TIDAK
 * MELEBIHI nilai maksimum tertentu (mis. total owned_equity_percent shareholder
 * tidak boleh > 100).
 *
 * Pola "total across array items harus match target" — pakai epsilon agar aman
 * dari floating-point. Lihat konvensi di CLAUDE.md.
 *
 * Contoh: new SumAtMost('owned_equity_percent', 100)
 */
class SumAtMost implements ValidationRule
{
    public function __construct(
        private string $key,
        private float $max,
        private float $epsilon = 0.001,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return; // biarkan rule 'array' yang menangani tipe salah
        }

        $sum = 0.0;
        foreach ($value as $item) {
            $sum += (float) ($item[$this->key] ?? 0);
        }

        if ($sum - $this->max > $this->epsilon) {
            $fail("Total :attribute ({$this->key}) tidak boleh melebihi {$this->max} (saat ini {$sum}).");
        }
    }
}
