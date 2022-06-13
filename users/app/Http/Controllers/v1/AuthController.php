<?php

namespace App\Http\Controllers\v1;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */

    public function register(Request $request, User $user) : JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'unique:users,email', 'email'],
            'password' => ['required', 'string', 'confirmed'],
            'name' => ['required', 'string']
        ]);

        try {
            $user = $user->service()->assignAttributes(
                $data['email'],
                $data['name'],
                $data['password']
            )->getUser();
        } catch (\Exception $e) {
            reportError($e);

            return $this->errorResponse(
                __('messages.Something went wrong'),
                500
            );
        }

        event(new Registered($user));

        return $this->successResponse(
            __('messages.Registered successfully. Check your mailbox and confirm email')
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function login(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string']
        ]);

        if(auth('web')->attempt($data) && auth('web')->user()->email_verified_at) {

            return $this->successResponse([
                'user' => new UserResource(
                    auth('web')->user()
                ),
                'access_token' => auth('web')->user()->createToken('auth')->plainTextToken
            ]);
        }

        return $this->errorResponse(
            __('messages.Provided credentials are not valid'),
            422
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function forgotPassword(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $status = Password::sendResetLink([
            'email' => $data['email']
        ]);

        if($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(
                __('messages.Link for reset password has been sent on email.')
            );
        }

        return $this->errorResponse(
            __('messages.Provided credentials are not valid'),
            401
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function resetPassword(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed'],
        ]);

        $status = Password::reset($data, static function (User $user, $password) {
            $user->password = Hash::make($password);
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(
                __('messages.Password has been reset')
            );
        }

        return $this->errorResponse(
            __('messages.User not found or token is invalid'),
            401
        );
    }

    /**
     * @param EmailVerificationRequest $request
     * @return JsonResponse
     */

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return $this->successResponse(
            __('messages.Address email has been verified')
        );
    }

    /**
     * @return JsonResponse
     */

    public function logout() : JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->successResponse(
            __('messages.User logged out')
        );
    }
}
