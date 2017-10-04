<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 11:39 PM
 */

namespace onepeople\router;


interface HandlerConverter
{
    /**
     * @param string $method
     * @param string $route
     * @param callable $handler
     */
    public function convertRoute(&$method, &$route, &$handler);
}