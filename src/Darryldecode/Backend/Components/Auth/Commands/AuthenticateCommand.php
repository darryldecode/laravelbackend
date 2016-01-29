<?php

namespace Darryldecode\Backend\Components\Auth\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Validation\Factory;
use Darryldecode\Backend\Components\User\Models\Throttle;
use Darryldecode\Backend\Components\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthenticateCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $email;
    /**
     * @var null
     */
    private $password;
    /**
     * @var bool
     */
    private $remember;

    /**
     * @param string $email
     * @param string $password
     * @param bool $remember
     */
    public function __construct($email, $password, $remember = false)
    {
        parent::__construct();
        $this->email = $email;
        $this->password = $password;
        $this->remember = $remember;
        $this->args = func_get_args();
    }

    /**
     * @param Factory $validator
     * @param Throttle $throttle
     * @param User $user
     * @return CommandResult
     */
    public function handle(Factory $validator, Throttle $throttle, User $user)
    {
        // validate data
        $validationResult = $validator->make(array(
            'email' => $this->email,
            'password' => $this->password,
        ), array(
            'email' => 'required|email',
            'password' => 'required',
        ));

        if( $validationResult->fails() )
        {
            return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
        }

        // we need to flag that a user that is authenticating has no throttle entry by default
        $throttleEntry = false;

        // check if the user exist and get its throttle entry
        // then we will check if the user is suspended or banned
        if( $user = $user->where('email',$this->email)->first() )
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

                    return new CommandResult(false, 'This account is currently suspended. You can login after '.$minsRemaining.' minutes.', null, 401);
                }
            }

            // if the user is currently banned, no need to do anything
            // we will just redirect it back with banned notice
            elseif( $throttleEntry->isBanned() )
            {
                return new CommandResult(false, "This account is currently banned.", null, 401);
            }
        }

        // attempt to login
        if (Auth::attempt(array('email'=>$this->email, 'password'=>$this->password), $this->remember))
        {
            $throttleEntry->clearLoginAttempts();

            return new CommandResult(true, "Authentication Successful.", Auth::user(), 200);
        }

        // login attempt failed, let's increment login attempt
        if( $throttleEntry )
        {
            $throttleEntry->addLoginAttempt();

            return new CommandResult(false, "These credentials do not match our records. Login attempt remaining: ".$throttleEntry->getRemainingLoginAttempts(), null, 401);
        }

        return new CommandResult(false, "These credentials do not match our records.", null, 401);
    }
}