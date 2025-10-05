<?php

declare(strict_types=1);

namespace App\Finance\Http\Requests;

use Domain\Identity\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ListTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() instanceof User;
    }
}
