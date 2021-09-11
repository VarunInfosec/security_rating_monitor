<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $user = $this->getUser($request);

        if ($user->hasVerifiedEmail()) {
            return $this->generateResponse("Email already verified.", true, $request);
        }

        $verifyUser = $user->markEmailAsVerified();

        if ($verifyUser) {
            event(new Verified($user));

            if($user->status == 0){
                $user->status = 1;
                $user->save();
            }
        }

        return $this->generateResponse("Email verified successfully.", true, $request);
    }

    private function generateResponse($message, $status, $request){
        $user = $this->getUser($request);
        
        return response([
            "message" => $message,
            "status"  => $status,
            "user"    => $user
        ], 201);
    }

    private function getUser($request){ return User::find($request->route('id')); }
}
