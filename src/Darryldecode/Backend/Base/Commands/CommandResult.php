<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/25/2015
 * Time: 11:59 AM
 */

namespace Darryldecode\Backend\Base\Commands;


use Illuminate\Support\Collection;

class CommandResult {

    /**
     * @var bool
     */
    protected $success = false;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $message = 'No message set.';

    /**
     * @var null|mixed
     */
    protected $data;

    static $responseForbiddenMessage = "Not enough permission.";
    static $responseInternalErrorMessage = "An error has occurred on the server.";

    /**
     * Command result object constructor
     *
     * @param bool $success
     * @param string $message
     * @param null $data
     * @param int $statusCode
     */
    public function __construct($success = false, $message = '', $data = null, $statusCode = 500)
    {
        $this->success = $success;
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * determine if command transaction was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * the command result message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * the command result returned data
     *
     * @return null
     */
    public function getData()
    {
        if( is_null($this->data) ) return new Collection();

        if( is_array($this->data) ) return new Collection($this->data);

        return $this->data;
    }
}