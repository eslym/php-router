<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 11:36 PM
 */

namespace onepeople\router;


interface RouteProvider
{
    /**
     * @return iterable
     */
    public function getRoutes();
}