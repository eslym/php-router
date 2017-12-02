<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 9:52 PM
 */

namespace onepeople\router;

use BadMethodCallException;

/**
 * Class GlobalRouter
 * @package onepeople\router
 * @method static get(string $route, callable $handler)
 * @method static post(string $route, callable $handler)
 * @method static put(string $route, callable $handler)
 * @method static patch(string $route, callable $handler)
 * @method static delete(string $route, callable $handler)
 * @method static addRoute(string $method, string $route, callable $handler)
 * @method static addRouteAuto(mixed $handler)
 * @method static array match(string $uri=null, string $method=null)
 * @method static mixed route(string $uri=null, string $method=null)
 */
final class GlobalRouter
{
    private static $instance;

    public static function __callStatic($name, $arguments)
    {
        if(!self::$instance){
            self::$instance = new Router();
        }
        if(method_exists(self::$instance, $name)){
            return self::$instance->$name(...$arguments);
        }
        throw new BadMethodCallException("GlobalRouter::$name() does not exist.");
    }
}