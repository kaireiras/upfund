<?php

namespace App\Http\Requests;

use App\Rules\SumAtMost;
use App\Rules\SumEquals;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProjectRequest extends FormRequest
{
    /**
     * Otorisasi ditangani middleware auth:sanctum di route; user apa pun yang
     * terautentikasi boleh membuat project (jadi owner).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi POST /projects.
     *
     * Penamaan field mengikuti mockup FE (video_pitch_url, cover_image_url,
     * company_valuation, owned_equity_percent, release_budget_percent, start/end
     * _date). Pemetaan ke kolom DB dilakukan di ProjectService, bukan di sini.
     */
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:100'],
            'description'       => ['required', 'string'],
            'video_pitch_url'   => ['required', 'url', 'max:1024'],
            'cover_image_url'   => ['required', 'url', 'max:1024'],
            'company_valuation' => ['required', 'integer', 'min:0'],
            'funding_target'    => ['required', 'integer', 'min:0'],

            'categories'        => ['required', 'array', 'min:1', 'max:5'],
            'categories.*'      => ['required', 'integer', 'distinct', 'exists:categories,id'],

            // owned_equity_percent = numeric (kolom shareholders.share decimal(5,2)),
            // maksimal 2 desimal agar tidak melebihi presisi kolom.
            'shareholders'                        => ['required', 'array', 'min:1', new SumAtMost('owned_equity_percent', 100)],
            'shareholders.*.name'                 => ['required', 'string', 'max:255'],
            'shareholders.*.owned_equity_percent' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],

            // release_budget_percent = integer (kolom milestones.budget integer),
            // total HARUS PAS 100.
            'milestones'                          => ['required', 'array', 'min:1', new SumEquals('release_budget_percent', 100)],
            'milestones.*.title'                  => ['required', 'string', 'max:100'],
            'milestones.*.description'            => ['required', 'string'],
            'milestones.*.start_date'             => ['required', 'date'],
            'milestones.*.end_date'               => ['required', 'date', 'after:milestones.*.start_date'],
            'milestones.*.release_budget_percent' => ['required', 'integer', 'between:0,100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'shareholders' => 'shareholders',
            'milestones'   => 'milestones',
        ];
    }

    public function messages(): array
    {
        return [
            'categories.max' => 'Maksimal 5 kategori per project.',
        ];
    }

    /**
     * Kembalikan envelope error seragam ({status:"error", ...}) alih-alih bentuk
     * default Laravel, agar konsisten dengan standar respons API (lihat CLAUDE.md).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'The given data was invalid.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
