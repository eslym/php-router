<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/10/17
 * Time: 2:17 PM
 */

namespace onepeople\router;


use ArrayAccess;
use BadMethodCallException;

class Query implements ArrayAccess
{
    /**
     * @param string $query_string
     * @return Query
     */
    public static function parse($query_string){
        if(!is_string($query_string)){
            return new Query();
        }
        mb_parse_str($query_string, $data);
        return new Query($data);
    }

    private $__value;
    private $__exist;

    private function __construct($data = null)
    {
        $this->__exist = func_num_args() > 0;
        $this->__value = $data;
    }

    /**
     * @return bool
     */
    public function exist(){
        return $this->__exist;
    }

    /**
     * @return mixed
     */
    public function value(){
        return $this->__value;
    }

    /**
     * @return bool
     */
    public function isString(){
        return is_string($this->__value);
    }

    /**
     * @return bool
     */
    public function isInt(){
        return is_int($this->__value);
    }

    /**
     * @return bool
     */
    public function isFloat(){
        return is_float($this->__value);
    }

    /**
     * @return bool
     */
    public function isArray(){
        return is_array($this->__value);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return key_exists($offset, $this->__value);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if(key_exists($offset, $this->__value)){
            return new Query($this->__value[$offset]);
        }else{
            return new Query();
        }
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException();
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException();
    }
}