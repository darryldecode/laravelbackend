<?php

namespace Darryldecode\Backend\Components\Auth\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController {

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * displays the login page
     *
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        return view('authManager::login');
    }

    /**
     * handle login post request
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->has('remember'))) {
            return redirect()->intended(config('backend.backend.base_url').'/dashboard');
        }

        return redirect(config('backend.backend.login_route'))
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        Auth::logout();

        return redirect(config('backend.backend.login_route'));
    }
}