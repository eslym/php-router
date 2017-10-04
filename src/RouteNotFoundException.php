<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/3/17
 * Time: 8:08 AM
 */

namespace onepeople\router;

use Exception;

class RouteNotFoundException extends Exception
{
    private $method;
    private $uri;

    public function __construct($method, $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
        parent::__construct("$method $uri");
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}