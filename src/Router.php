<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/3/17
 * Time: 1:24 AM
 */

namespace onepeople\router;

use Generator;
use ReflectionClass;
use Reflector;
use TypeError;

/**
 * Class Router
 * @package onepeople\router
 */
class Router
{

    // dear PhpStrom this is not a fucking html tag
    const /** @noinspection HtmlUnknownTag */
        PARAM_REGEX = '/\$(?<ignore>!)?(?:(?:(?<name>[a-z][a-z0-9_]*):?)|:)(?<type>num|int|string|hex|alpha|alnum|path|(?<enum>enum|list))?(?(<enum>)\[(?<list>(?:[^\[\]\/]|\|)*?)\]|)(?<optional>\?)?(?:;?)/';

    const PATH_REGEX = '/(?:\/|^)([^\/]*)(?:\/$)?/';

    const PARAM_TYPE_MAP = [
        'num' => '\d+',
        'int' => '\d+',
        'string' => '[^\\/]+',
        'alpha' => '[a-zA-Z]+',
        'alnum' => '[0-9a-zA-Z]+',
        'hex' => '[0-9a-fA-F]+',
        'path' => '[\s\S]+'
    ];

    public function __invoke()
    {
        $this->route();
    }

    /**
     * @param string $route
     * @return string
     */
    public static function buildRegex($route){
        $regex = '/^';
        foreach (self::parse($route) as $path){
            $regex.=$path->skippable ? "(?:\/(?!\/)$path->regex)?" : '\/'.$path->regex;
        }
        return $regex.'\/?$/';
    }

    /**
     * @param Reflector $reflector
     * @return Generator
     */
    public static function findActionRoutes($reflector){
        if(method_exists($reflector, 'getDocComment')){
            $regex = '/(?:^|\\s)@(?:action|route)[ \\t]+([a-z]+)[ \\t]+([^\\s]+)(?:\\s|$)/i';
            $doc = $reflector->getDocComment();
            foreach(preg_split('/(\r?\n|\n?\r)/', $doc) as $line){
                if(preg_match($regex, $line, $matches)){
                    yield [$matches[1], $matches[2]];
                }
            }
        }
    }

    /**
     * @param Reflector $reflector
     * @return Generator
     */
    public static function findBaseRoutes($reflector){
        if(method_exists($reflector, 'getDocComment')){
            $regex = '/(?:^|\\s+)@BaseRoute[ \\t]+([^\\s]+)(?:\\s|$)/i';
            $doc = $reflector->getDocComment();
            foreach(preg_split('/(\r?\n|\n?\r)/', $doc) as $line){
                if(preg_match($regex, $line, $matches)){
                    yield $matches[1];
                }
            }
        }
    }

    /**
     * @param string $route
     * @return Generator
     */
    private static function parse($route){
        foreach (self::splitPathParse($route) as $parts){
            $path = (object)['skippable'=>true, 'regex'=>''];
            foreach ($parts as $part){
                if(is_object($part)){
                    $path->regex.='(';
                    if($part->ignore){
                        $path->regex.='?:';
                    }else if(key_exists('name', $part)){
                        $path->regex.="?<$part->name>";
                    }
                    if(key_exists($part->type, self::PARAM_TYPE_MAP)){
                        $path->regex.=self::PARAM_TYPE_MAP[$part->type];
                    }else{
                        foreach ($part->list as &$v){
                            $v = preg_quote($v);
                        }
                        unset($v);
                        $path->regex.=join('|', $part->list);
                    }
                    $path->regex.=')';
                    if($part->optional){
                        $path->regex.='?';
                    }else{
                        $path->skippable = false;
                    }
                }else{
                    $path->skippable = false;
                    $path->regex .= preg_quote($part);
                }
            }
            yield $path;
        }
    }

    /**
     * @param string $route
     * @return Generator
     */
    private static function splitPathParse($route){
        foreach (self::splitPath($route) as $path){
            yield iterator_to_array(self::parseParam($path));
        }
    }

    /**
     * @param string $route
     * @return Generator
     */
    private static function parseParam($route){
        $index = 0;
        while(preg_match(self::PARAM_REGEX, $route, $matches, PREG_OFFSET_CAPTURE, $index)){
            if($len = $matches[0][1] - $index){
                yield substr($route, $index, $len);
            }
            $param = (object)[];
            $param->ignore = isset($matches['ignore']) && $matches['ignore'][1] >= 0;
            if(isset($matches['name']) && $matches['name'][1] >= 0){
                $param->name = $matches['name'][0];
            }
            if(isset($matches['type']) && $matches['type'][1] >= 0){
                $param->type = $matches['type'][0];
            }else{
                $param->type = 'string';
            }
            if(isset($matches['list']) && $matches['list'][1] >= 0){
                $param->list = array_unique(explode('|', $matches['list'][0]));
                sort($param->list);
            }
            $param->optional = isset($matches['optional']) && $matches['optional'][1] >= 0;
            yield $param;
            $index = $matches[0][1] + strlen($matches[0][0]);
        }
        if($len = strlen($route) - $index){
            yield substr($route, $index, $len);
        }
    }

    /**
     * @param string $route
     * @return Generator
     */
    private static function splitPath($route){
        preg_match_all(
            self::PATH_REGEX,
            $route, $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as list(,$path)){
            yield $path;
        }
    }

    private $routes = [];

    /**
     * @param string $route
     * @param callable $handler
     */
    public function get($route, $handler){
        $this->addRoute(
            'GET',
            $route,
            $handler
        );
    }

    /**
     * @param string $route
     * @param callable $handler
     */
    public function post($route, $handler){
        $this->addRoute(
            'POST',
            $route,
            $handler
        );
    }

    /**
     * @param string $route
     * @param callable $handler
     */
    public function put($route, $handler){
        $this->addRoute(
            'PUT',
            $route,
            $handler
        );
    }

    /**
     * @param string $route
     * @param callable $handler
     */
    public function patch($route, $handler){
        $this->addRoute(
            'PATCH',
            $route,
            $handler
        );
    }

    /**
     * @param string $route
     * @param callable $handler
     */
    public function delete($route, $handler){
        $this->addRoute(
            'DELETE',
            $route,
            $handler
        );
    }

    /**
     * @param string $method
     * @param string $route
     * @param callable $handler
     */
    public function addRoute($method, $route, $handler){
        $method = strtoupper($method);
        if(!key_exists($method, $this->routes)){
            $this->routes[$method] = [];
        }
        $regex = self::buildRegex($route);
        if(!key_exists($regex, $this->routes[$method])){
            $this->routes[$method][$regex] = [];
        }
        $this->routes[$method][$regex][]=$handler;
    }

    /**
     * @param string $uri [optional]
     * @param string $method [optional]
     * @return array
     */
    public function match($uri = null, $method = null){
        if($uri === null){
            $uri = $_SERVER['REQUEST_URI'];
        }
        if($method === null){
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $method = strtoupper($method);
        if(!key_exists($method, $this->routes)){
            return [];
        }
        $result = [];
        foreach($this->routes[$method] as $regex => $handlers){
            if($match = MatchResult::tryMatch($regex, $method, $uri)) {
                foreach ($handlers as $handler){
                    $result[]= (clone $match)->setHandler($handler);
                }
            }
        }
        return $result;
    }

    /**
     * @param ?string $uri
     * @param ?string $method
     * @return mixed
     * @throws
     */
    public function route($uri = null, $method = null){
        if($uri === null){
            $uri = $_SERVER['REQUEST_URI'];
        }
        if($method === null){
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $method = strtoupper($method);
        $handlers = $this->match($uri, $method);
        if(count($handlers) > 0){
            /** @var $h MatchResult */
            $h = $handlers[0];
            if(is_callable($h->getHandler())){
                return call_user_func_array($h->getHandler(), $h->getParams());
            }else{
                $handler = $h->getHandler();
                $handler = is_object($handler) ?
                    '(object)'.get_class($handler) :
                    '('.gettype($handler).')'.$handler;
                throw new TypeError("'$handler' is not callable.");
            }
        }
        throw new HandlerNotFound($method, $uri);
    }

    /**
     * @param callable $handler
     * @throws TypeError
     */
    public function addRouteAuto($handler){
        if(is_callable($handler)){
            $callable = new ReflectionCallable($handler);
            foreach (self::findActionRoutes($callable) as list($method, $route)){
                $this->addRoute($method, $route, $handler);
            }
        }else if(is_object($handler) || class_exists($handler)){
            $class = new ReflectionClass($handler);
            $bases = iterator_to_array(self::findBaseRoutes($class));
            foreach($class->getMethods() as $func){
                if(!$func->isPublic()) continue;
                foreach (self::findActionRoutes($func) as list($method, $route)){
                    foreach ($bases as $base){
                        $fullRoute = $base.$route;
                        $callback = [$handler, $func->getName()];
                        if(is_callable($converter = [$handler, 'convertRoute'])){
                            call_user_func_array(
                                $converter, [&$method, &$fullRoute, &$callback]);
                        }
                        $this->addRoute($method, $fullRoute, $callback);
                    }
                }
            }
            if(is_callable($routes = [$handler, 'getRoutes'])){
                foreach (call_user_func($routes) as $params){
                    $this->addRoute(...$params);
                }
            }
        }
    }
}