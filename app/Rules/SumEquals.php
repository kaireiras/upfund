<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Memvalidasi bahwa JUMLAH sebuah kolom numerik di seluruh item array PAS sama
 * dengan nilai target tertentu (mis. total release_budget_percent milestone = 100).
 *
 * Pola "total across array items harus match target" — pakai epsilon agar aman
 * dari floating-point (0.1 + 0.2 != 0.3). Lihat konvensi di CLAUDE.md.
 *
 * Contoh: new SumEquals('release_budget_percent', 100)
 */
class SumEquals implements ValidationRule
{
    public function __construct(
        private string $key,
        private float $target,
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

        if (abs($sum - $this->target) > $this->epsilon) {
            $fail("Total :attribute ({$this->key}) harus tepat {$this->target} (saat ini {$sum}).");
        }
    }
}
