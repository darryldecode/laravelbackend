<?php namespace Darryldecode\Backend\Utility;

class Helpers {

    /**
     * Converts timestamp to time ago
     * from: http://css-tricks.com/snippets/php/time-ago-function/
     *
     * @note: code modified by darrylcoder (added strtotime to prevent string error)
     *
     * @param $time
     * @return string
     */
    public static function ago($time)
    {
        $time = strtotime($time);

        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();

        $difference     = $now - $time;
        $tense         = "ago";

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j] ago ";
    }

    /**
     * check if variable is set and has value, return a default value
     *
     * @param $var
     * @param null|mixed $default
     * @return null
     */
    public static function issetAndHasValueOrAssignDefault(&$var, $default = null)
    {
        if( is_null($var) ) return $default;

        if( empty($var) ) return $default;

        if( (isset($var)) && ($var!='') ) return $var;

        return $default;
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * REF: https://core.trac.wordpress.org/browser/tags/3.9.1/src/wp-includes/functions.php#L270
     *
     * @param mixed $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Defaults true.
     * @return bool False if not serialized and true if it was.
     */
    public static function is_serialized( $data, $strict = true ) {
        // if it isn't a string, it isn't serialized
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' == $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace )
                return false;
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 )
                return false;
            if ( false !== $brace && $brace < 4 )
                return false;
        }
        $token = $data[0];
        switch ( $token ) {
            case 's' :
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
                break;
            case 'a' :
            case 'O' :
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
        }
        return false;
    }

    /**
     * just a quick stuff to check if extension is image
     *
     * @param $extension
     * @return bool
     */
    public static function extensionIsImage($extension)
    {
        $imageExtensions = array(
            'jpg',
            'jpeg',
            'JPEG',
            'JPG',
            'gif',
            'GIF',
            'png',
        );

        if( in_array($extension, $imageExtensions) ) return true;

        return false;
    }

    /**
     * returns readable month string
     * ex. monthToString(4) will output 'april'
     *
     * @param $month
     * @return string
     */
    public static function monthToString($month)
    {
        if($month ==1) return 'January';
        if($month ==2) return 'February';
        if($month ==3) return 'March';
        if($month ==4) return 'April';
        if($month ==5) return 'May';
        if($month ==6) return 'June';
        if($month ==7) return 'July';
        if($month ==8) return 'August';
        if($month ==9) return 'September';
        if($month ==10) return 'October';
        if($month ==11) return 'November';
        if($month ==12) return 'December';
    }

    /**
     * redirect to dashboard
     *
     * @param string $url
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function redirectBackend($url = null)
    {
        if( is_null($url) ) self::redirectDashboard();

        return redirect()->intended(config('backend.backend.base_url').'/'.trim($url,'/'));
    }

    /**
     * redirect to dashboard
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function redirectDashboard()
    {
        return redirect()->intended(config('backend.backend.base_url').'/dashboard');
    }

    /**
     * redirect to login page
     *
     * @param null|array $queryString
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public static function redirectLogin($queryString = null)
    {
        if( is_null($queryString) ) return redirect(config('backend.backend.base_url').'/'.config('backend.backend.login_route'));

        $queryString = http_build_query($queryString);

        return redirect(config('backend.backend.base_url').'/'.config('backend.backend.login_route').'?'.$queryString);
    }

    /**
     * returns the dashboard route
     *
     * @return string
     */
    public static function getDashboardRoute()
    {
        return config('backend.backend.base_url').'/dashboard';
    }

    /**
     * returns the login route
     *
     * @return string
     */
    public static function getLoginRoute()
    {
        return trim(config('backend.backend.base_url'),'/').'/'.trim(config('backend.backend.login_route'),'/');
    }
} 