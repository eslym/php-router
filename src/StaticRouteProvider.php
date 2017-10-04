<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 11:37 PM
 */

namespace onepeople\router;


interface StaticRouteProvider
{
    /**
     * @return iterable
     */
    public static function getRoutes();
}