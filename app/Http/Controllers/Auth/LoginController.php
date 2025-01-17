<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
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
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

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
            'cod_email' => (($request->email == 'andrei.dima@usm.ro') || ($request->email == 'alextca54@gmail.com') || ($request->email == 'ionutsv_2003@yahoo.com') || ($request->email == 'razvanslusariuc0@gmail.com')) ? 'nullable|string' : 'required|string', // Andrei - adaugat sa fie obligatoriu - but not for the Andrei user
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
        if (($request->email !== 'andrei.dima@usm.ro') && ($request->email !== 'alextca54@gmail.com') && ($request->email !== 'ionutsv_2003@yahoo.com') && ($request->email !== 'razvanslusariuc0@gmail.com')) { // pentru ceilalti useri, este necesar si cod_email de verificat
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
    }
}
