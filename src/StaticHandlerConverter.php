<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 11:41 PM
 */

namespace onepeople\router;


interface StaticHandlerConverter
{
    /**
     * @param string $method
     * @param string $route
     * @param callable $handler
     */
    public static function convertRoute(&$method, &$route, &$handler);
}