<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 9:19 PM
 */

namespace Darryldecode\Backend\Components\User\Models;

use Darryldecode\Backend\Components\User\Exceptions\UserBannedException;
use Darryldecode\Backend\Components\User\Exceptions\UserSuspendedException;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Throttle extends BaseModel {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'throttle';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'permissions',
        'user_id',
        'banned',
        'suspended',
        'attempts',
        'ip_address',
        'last_attempt_at',
        'suspended_at',
        'banned_at'
    ];

    /**
     * Attempt limit.
     *
     * @var int
     */
    protected static $attemptLimit = 5;

    /**
     * Suspension time in minutes.
     *
     * @var int
     */
    protected static $suspensionTime = 15;

    /**
     * User relationship for the throttle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Darryldecode\Backend\Components\User\Models\User', 'user_id');
    }

    /**
     * Check user throttle status.
     *
     * @return bool
     * @throws \Darryldecode\Backend\Components\User\Exceptions\UserBannedException
     * @throws \Darryldecode\Backend\Components\User\Exceptions\UserSuspendedException
     */
    public function check()
    {
        if ($this->isBanned())
        {
            throw new UserBannedException(sprintf(
                'User [%s] has been banned.',
                $this->user()->getResults()->first_name
            ));
        }
        if ($this->isSuspended())
        {
            throw new UserSuspendedException(sprintf(
                'User [%s] has been suspended.',
                $this->user()->getResults()->first_name
            ));
        }
        return true;
    }

    /**
     * Get the number of login attempts a user has left before suspension.
     *
     * @return int
     */
    public function getRemainingLoginAttempts()
    {
        return static::getAttemptLimit() - $this->getLoginAttempts();
    }

    /**
     * Get the current amount of attempts.
     *
     * @return int
     */
    public function getLoginAttempts()
    {
        return $this->attempts;
    }

    /**
     * Check if the user is suspended.
     *
     * @return bool
     */
    public function isSuspended()
    {
        if ($this->suspended and $this->suspended_at)
        {
            return (bool) $this->suspended;
        }
        return false;
    }

    /**
     * Suspend the user associated with the throttle
     *
     * @return void
     */
    public function suspend()
    {
        if ( ! $this->suspended)
        {
            $this->suspended    = true;
            $this->suspended_at = $this->freshTimeStamp();
            $this->save();
        }
    }

    /**
     * Unsuspend the user.
     *
     * @return void
     */
    public function unSuspend()
    {
        if ($this->suspended)
        {
            $this->attempts        = 0;
            $this->last_attempt_at = null;
            $this->suspended       = false;
            $this->suspended_at    = null;
            $this->save();
        }
    }

    /**
     * Get mutator for the suspended property.
     *
     * @param  mixed  $suspended
     * @return bool
     */
    public function getSuspendedAttribute($suspended)
    {
        return (bool) $suspended;
    }
    /**
     * Get mutator for the banned property.
     *
     * @param  mixed  $banned
     * @return bool
     */
    public function getBannedAttribute($banned)
    {
        return (bool) $banned;
    }

    /**
     * Set attempt limit.
     *
     * @param  int  $limit
     */
    public static function setAttemptLimit($limit)
    {
        static::$attemptLimit = (int) $limit;
    }

    /**
     * Get attempt limit.
     *
     * @return  int
     */
    public static function getAttemptLimit()
    {
        return static::$attemptLimit;
    }
    /**
     * Set suspension time.
     *
     * @param  int  $minutes
     */
    public static function setSuspensionTime($minutes)
    {
        static::$suspensionTime = (int) $minutes;
    }

    /**
     * Get suspension time.
     *
     * @return  int
     */
    public static function getSuspensionTime()
    {
        return static::$suspensionTime;
    }

    /**
     * Ban the user.
     *
     * @return void
     */
    public function ban()
    {
        if ( ! $this->banned)
        {
            $this->banned = true;
            $this->banned_at = $this->freshTimeStamp();
            $this->save();
        }
    }

    /**
     * Unban the user.
     *
     * @return void
     */
    public function unban()
    {
        if ($this->banned)
        {
            $this->banned = false;
            $this->banned_at = null;
            $this->save();
        }
    }

    /**
     * Check if user is banned
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * Add a new login attempt.
     *
     * @return void
     */
    public function addLoginAttempt()
    {
        $this->attempts++;
        $this->last_attempt_at = $this->freshTimeStamp();

        if ($this->getLoginAttempts() >= static::$attemptLimit)
        {
            $this->suspend();
        }
        else
        {
            $this->save();
        }
    }

    /**
     * Clear all login attempts
     *
     * @return void
     */
    public function clearLoginAttempts()
    {
        if ($this->getLoginAttempts() == 0 or $this->suspended)
        {
            return;
        }

        $this->attempts        = 0;
        $this->last_attempt_at = null;
        $this->suspended       = false;
        $this->suspended_at    = null;
        $this->save();
    }
}