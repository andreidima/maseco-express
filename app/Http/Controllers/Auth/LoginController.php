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
use Illuminate\Support\Str;
use Throwable;

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

        if ($user && $user->hasPermission('dashboard')) {
            return route('dashboard');
        }

        if ($user) {
            $menuUrl = MainNavigation::firstAccessibleUrlFor($user);

            if ($menuUrl) {
                return $menuUrl;
            }
        }

        return $this->redirectTo;
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = $this->guard()->user();
        $defaultRedirect = $this->redirectPath();
        $intendedUrl = $request->session()->pull('url.intended');

        if ($response = $this->authenticated($request, $user)) {
            return $response;
        }

        $intendedEvaluation = $this->evaluateIntendedRedirect($intendedUrl, $user);

        if ($intendedEvaluation['allowed']) {
            return redirect()->to($intendedUrl);
        }

        if ($intendedUrl) {
        }

        return redirect()->to($defaultRedirect);
    }

    protected function evaluateIntendedRedirect(?string $intendedUrl, ?User $user): array
    {
        if (! $intendedUrl) {
            return ['allowed' => false, 'reason' => 'missing-intended-url'];
        }

        if (! $user) {
            return ['allowed' => false, 'reason' => 'missing-user'];
        }

        $path = parse_url($intendedUrl, PHP_URL_PATH) ?? '/';

        try {
            $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));
        } catch (Throwable $exception) {
            Log::debug('Login intended redirect route match failed', [
                'user_id' => $user->id,
                'intended_url' => $intendedUrl,
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]);

            return ['allowed' => false, 'reason' => 'unmatched-route'];
        }

        $missingPermissions = [];
        $missingRoles = [];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (Str::startsWith($middleware, 'permission:')) {
                $requiredPermissions = preg_split('/[|,]/', Str::after($middleware, 'permission:'), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($requiredPermissions as $permission) {
                    $permission = trim($permission);

                    if ($permission === '') {
                        continue;
                    }

                    if (! $user->hasPermission($permission)) {
                        $missingPermissions[] = $permission;
                    }
                }
            }

            if (Str::startsWith($middleware, 'role:')) {
                $requiredRoles = preg_split('/[|,]/', Str::after($middleware, 'role:'), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($requiredRoles as $role) {
                    $role = trim($role);

                    if ($role === '') {
                        continue;
                    }

                    if (! $user->hasRole($role)) {
                        $missingRoles[] = $role;
                    }
                }
            }
        }

        if (! empty($missingPermissions) || ! empty($missingRoles)) {
            return [
                'allowed' => false,
                'reason' => 'failed-access-check',
                'missing_permissions' => array_values(array_unique($missingPermissions)),
                'missing_roles' => array_values(array_unique($missingRoles)),
            ];
        }

        return ['allowed' => true];
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
