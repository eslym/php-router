<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/4/17
 * Time: 7:04 PM
 */

namespace onepeople\router;


class MatchResult
{
    private $regex;
    private $method;
    private $uri;
    private $handler;
    private $matches;
    private $namedParams;
    private $params;

    /**
     * MatchResult constructor.
     * @param string $regex
     * @param string $method
     * @param string $uri
     * @return bool|MatchResult
     */
    public static function tryMatch($regex, $method, $uri){
        $self = new self();
        $self->method = $method;
        if(preg_match(
            $self->regex = $regex,
            $self->uri = $uri,
            $self->matches)
        ) {
            $self->namedParams = $self->matches;
            array_shift($self->namedParams);
            $remove = false;
            foreach ($self->namedParams as $key => $value) {
                if ($remove) {
                    unset($self->namedParams[$key]);
                }
                if (is_string($key)) {
                    $remove = true;
                }
            }
            $self->params = $self->matches;
            array_shift($self->params);
            foreach ($self->params as $key => $value) {
                if (is_string($key)) {
                    unset($self->params[$key]);
                }
            }
            return $self;
        }
        return false;
    }

    private function __construct()
    {

    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
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

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @return array
     */
    public function getNamedParams()
    {
        return $this->namedParams;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param callable $handler
     * @return $this
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    function __clone()
    {
        $clone = new self();
        foreach ($this as $prop => $val){
            $clone->{$prop} = $val;
        }
        return $clone;
    }
}