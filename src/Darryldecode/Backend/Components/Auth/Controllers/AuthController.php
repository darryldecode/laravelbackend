<?php

namespace Darryldecode\Backend\Components\Auth\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;

use Darryldecode\Backend\Components\User\Models\Throttle;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends BaseController {

    public function __construct()
    {
        parent::__construct();
        $this->middleware('backend.guest',array('except'=>'getLogout'));
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
     * @param Throttle $throttle
     * @param User $user
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request, Throttle $throttle, User $user)
    {
        $credentials = $request->only('email', 'password');

        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\Auth\Commands\AuthenticateCommand',
            $credentials
        );

        // if authentication is good
        if( $result->isSuccessful() )
        {
            if( $request->get('ru') != '' )
            {
                return redirect()->intended($request->get('ru'));
            }

            return redirect()->intended(Helpers::getDashboardRoute());
        }

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(array('errors' => $result->getMessage()));
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        Auth::logout();

        return redirect(Helpers::getLoginRoute());
    }
}