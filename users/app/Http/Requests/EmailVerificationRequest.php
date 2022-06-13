<?php

namespace App\Http\Requests;

use App\Enums\NotificationTokenProviderEnum;
use App\Events\ActionScriptEvent;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if($this->route('id') && $this->route('hash')) {
            $user = User::query()->find(
                $this->route('id')
            );

            if(!$user) {
                return false;
            }

            if(
                !hash_equals(
                    (string) $this->route('hash'),
                    hash('sha256', $user->getEmailForVerification())
                )
            ) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill(): void
    {
        $user = User::query()->find(
            $this->route('id')
        );

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return Validator
     */
    public function withValidator(Validator $validator): Validator
    {
        return $validator;
    }
}
