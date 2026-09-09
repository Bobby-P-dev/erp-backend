<?php

namespace App\Http\Requests\Auth;

use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['sometimes', 'string'],
            'nik' => ['required_without_all:login,email', 'string'],
            'email' => ['required_without_all:login,nik', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->input('login') ?? $this->input('email') ?? $this->input('nik');
        $user = null;
        $errorField = 'nik';

        // 1. Check if input is an Email or explicitly provided via 'email' field -> Supplier auth
        if ($this->filled('email') || filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $errorField = $this->filled('email') ? 'email' : ($this->filled('login') ? 'login' : 'email');

            $supplier = Supplier::where('email', $identifier)->first();

            if ($supplier) {
                if (! $supplier->is_active) {
                    RateLimiter::hit($this->throttleKey());

                    throw ValidationException::withMessages([
                        $errorField => __('Supplier account is inactive.'),
                    ]);
                }

                $user = User::where('supplier_id', $supplier->id)->first();
            }
        } else {
            // 2. Employee auth via NIK
            $errorField = $this->filled('nik') ? 'nik' : ($this->filled('login') ? 'login' : 'nik');

            $employee = Employee::where('nik', $identifier)->first();

            if ($employee) {
                if (! $employee->is_active) {
                    RateLimiter::hit($this->throttleKey());

                    throw ValidationException::withMessages([
                        $errorField => __('Employee account is inactive.'),
                    ]);
                }

                $user = User::where('employee_id', $employee->id)->first();
            }
        }

        if (! $user || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $errorField => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $errorField = $this->filled('login') ? 'login' : ($this->filled('email') ? 'email' : 'nik');

        throw ValidationException::withMessages([
            $errorField => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->input('login') ?? $this->input('email') ?? $this->input('nik') ?? '';

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
