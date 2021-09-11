<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request){
        // $request->fulfill();

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        if(!empty($user) && !empty($user->email_verified_at)){
            return response([
                'message' => "Email Verified.",
                'user' => Auth::user()
            ], 201);
        }

        return response([
            'message' => "Email Not Verified."
        ], 401);
    }
}
