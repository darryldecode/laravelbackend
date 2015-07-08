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
        $throttleEntry = false;

        // check if the user exist and get its throttle entry
        // then we will check if the user is suspended or banned
        if( $user = $user->where('email',$credentials['email'])->first() )
        {
            if( ! $throttleEntry = $throttle->where('user_id',$user->id)->first() )
            {
                $throttleEntry = $throttle::create(array(
                    'user_id' => $user->id
                ));
            }

            // if the user is currently suspended, lets check its suspension is already expire
            // so we can clear its login attempts and attempt it to login again,
            // if not expired yet, then we will redirect it back with the suspended notice
            if( $throttleEntry->isSuspended() )
            {
                $now = Carbon::now();
                $suspendedUntil = Carbon::createFromTimeStamp(strtotime($throttleEntry->suspended_at))->addMinutes($throttle->getSuspensionTime());

                if( $now > $suspendedUntil )
                {
                    $throttleEntry->clearLoginAttempts();
                    $throttleEntry->unSuspend();
                }
                else
                {
                    $minsRemaining = $now->diffInMinutes($suspendedUntil);

                    return redirect()->back()
                        ->withInput($request->only('email', 'remember'))
                        ->withErrors([
                            'email' => 'This account is currently suspended. You can login after '.$minsRemaining.' minutes.',
                        ]);
                }
            }

            // if the user is currently banned, no need to do anything
            // we will just redirect it back with banned notice
            elseif( $throttleEntry->isBanned() )
            {
                return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'This account is currently banned.',
                    ]);
            }
        }

        // attempt to login
        if (Auth::attempt($credentials, $request->has('remember')))
        {
            $throttleEntry->clearLoginAttempts();

            if( $request->get('ru') != '' )
            {
                return redirect()->intended($request->get('ru'));
            }

            return redirect()->intended(Helpers::getDashboardRoute());
        }

        // login attempt failed, let's increment login attempt
        if( $throttleEntry )
        {
            $throttleEntry->addLoginAttempt();

            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'These credentials do not match our records. Login attempt remaining: '.$throttleEntry->getRemainingLoginAttempts(),
                ]);
        }

        return redirect()->back()
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

        return redirect(Helpers::getLoginRoute());
    }
}