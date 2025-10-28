<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\Navigation\MainNavigation;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected function redirectTo()
    {
        $user = Auth::user();

        Log::debug('Login redirectTo invoked', [
            'user_id' => $user?->id,
        ]);

        if ($user && $user->hasPermission('dashboard')) {
            Log::debug('Login redirecting to dashboard', [
                'user_id' => $user->id,
            ]);

            return route('dashboard');
        }

        if ($user) {
            $menuUrl = MainNavigation::firstAccessibleUrlFor($user);

            Log::debug('Login menu candidate resolved', [
                'user_id' => $user->id,
                'menu_url' => $menuUrl,
            ]);

            if ($menuUrl) {
                return $menuUrl;
            }
        }

        Log::debug('Login falling back to default redirect', [
            'user_id' => $user?->id,
            'fallback' => $this->redirectTo,
        ]);

        return $this->redirectTo;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest')->except('logout');
    }



    // Functii Andrei luate din Illuminate\Foundation\Auth\AuthenticatesUsers

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            'cod_email' => $this->shouldRequireEmailCode($request) ? 'required|string' : 'nullable|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        if ($this->shouldRequireEmailCode($request)) {
            return $request->only($this->username(), 'password', 'cod_email'); // Andrei - adaugat si cod_email
        }

        return $request->only($this->username(), 'password'); // Andrei - adaugat si cod_email
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Andrei - se sterge cod_email pentru ca utilizatorul sa fie fortat sa emita unul nou tura viitoare
        $user->cod_email = null;
        $user->save();

        return null;
    }

    private function shouldRequireEmailCode(Request $request): bool
    {
        $email = $request->input($this->username());

        if (! $email) {
            return true;
        }

        $exemptEmails = [
            'andrei.dima@usm.ro',
            'alextca54@gmail.com',
            'ionutsv_2003@yahoo.com',
            'razvanslusariuc0@gmail.com',
        ];

        if (in_array($email, $exemptEmails, true)) {
            return false;
        }

        $user = User::where($this->username(), $email)->first();

        if ($user && ($user->hasRole('mecanic') || $user->hasRole(4))) {
            return false;
        }

        return true;
    }
}
