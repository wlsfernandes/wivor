<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

   /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return '/index';
        } elseif ($user->hasRole('photographer')) {
            if (! $user->hasVerifiedEmail()) {
                return '/email/verify';
            }

            return $user->canAccessPhotographerArea()
                ? '/photographers/dashboard'
                : '/photographers/application-status';
        } elseif ($user->hasRole('customer')) {
            return '/customers/dashboard';
        }

        // Fallback
        return '/home';
    }

    /** Keep the stale admin dashboard URL from overriding the photographer redirect. */
    protected function authenticated(Request $request, $user)
    {
        $intendedUrl = $request->session()->get('url.intended');

        if ($user->hasRole('photographer')
            && ! $user->hasRole('admin')
            && $intendedUrl === url('/index')) {
            $request->session()->forget('url.intended');

            return redirect($this->redirectTo());
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
