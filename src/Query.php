<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/10/17
 * Time: 2:17 PM
 */

namespace onepeople\router;


use ArrayAccess;
use Countable;
use IteratorAggregate;
use ReflectionProperty;
use Traversable;
use TypeError;

class Query implements ArrayAccess, Countable, IteratorAggregate
{
    private static $proxy_class;

    /**
     * @param string $query_string
     * @return Query
     */
    public static function parse($query_string){
        $query = new Query();
        if(!is_string($query_string)){
            return $query;
        }
        if (function_exists('mb_parse_str')) {
            mb_parse_str($query_string, $data);
        } else {
            parse_str($query_string, $data);
        }
        $query->data = $data;
        $query->present = true;
        return $query;
    }

    protected $data = [];
    protected $present = false;

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __toString()
    {
        return $this->build();
    }

    /**
     * @return bool
     */
    public function isPresent()
    {
        return $this->present;
    }

    /**
     * @return Query
     */
    public function getParent()
    {
        return null;
    }

    /**
     * @param $nonExist
     * @return mixed
     */
    public function get($nonExist = null)
    {
        return $this->isPresent() ?
            $this->internalValue() :
            $nonExist;
    }

    /**
     * @return bool
     */
    public function isString(){
        return is_string($this->internalValue());
    }

    /**
     * @return bool
     */
    public function isInt(){
        return is_int($this->internalValue());
    }

    /**
     * @return bool
     */
    public function isFloat(){
        return is_float($this->internalValue());
    }

    /**
     * @return bool
     */
    public function isBool()
    {
        return is_bool($this->internalValue());
    }

    /**
     * @return bool
     */
    public function isArray(){
        return is_array($this->internalValue());
    }

    /**
     * @return string
     */
    public function build()
    {
        return http_build_query($this->internalValue());
    }

    /**
     * @param $value
     * @throws TypeError
     */
    public function set($value)
    {
        if (!is_scalar($value)) {
            if (is_object($value)) {
                $value = (array)$value;
            } else if (!is_array($value)) {
                throw new TypeError("Unexpected type.");
            }
        }
        $val = &$this->internalValue();
        $this->present = true;
        if (is_array($value)) {
            $val = [];
            foreach ($value as $k => $v) {
                $this[$k]->set($v);
            }
            return;
        }
        $val = $value;
    }

    public function delete()
    {
        $internal = &$this->internalValue();
        $internal = null;
        $this->present = false;
    }

    /**
     * @return mixed
     */
    protected function &internalValue()
    {
        return $this->data;
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
        if (!is_array($internal = &$this->internalValue())) {
            return false;
        }
        return key_exists($offset, $internal);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return Query
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return new self::$proxy_class($this, $offset);
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
     * @since 5.0.0.
     * @throws
     */
    public function offsetSet($offset, $value)
    {
        if (!is_scalar($value)) {
            if (is_object($value)) {
                $value = (array)$value;
            } else if (!is_array($value)) {
                throw new TypeError("Unexpected type.");
            }
        }
        $internal = &$this->internalValue();
        if (!is_array($internal)) {
            $internal = [];
        }
        if (is_null($offset)) {
            $internal[] = null;
            end($internal);
            $this[key($internal)]->set($value);
            rewind($internal);
        } else {
            $this[$offset]->set($value);
        }
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
        unset($this->internalValue()[$offset]);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        if (!is_array($this->internalValue())) {
            return 0;
        }
        return count($this->internalValue());
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        if (is_array($internal = &$this->internalValue())) {
            foreach ($internal as $k => $v) {
                yield $k => $this[$k];
            }
        }
    }
}

(function () {
    $prop = new ReflectionProperty(Query::class, 'proxy_class');
    $prop->setAccessible(true);
    $prop->setValue(new class extends Query
    {

        /**
         * @var Query
         */
        private $parent;

        public function __construct($parent, $offset)
        {
            $this->parent = $parent;
            $this->data = $offset;
        }

        public function set($value)
        {
            $internal = &$this->parent->internalValue();
            if (!is_array($internal)) {
                $internal = [];
            }
            parent::set($value);
        }

        public function delete()
        {
            parent::delete();
            unset($this->parent[$this->data]);
        }

        public function isPresent()
        {
            return isset($this->parent[$this->data]);
        }

        public function getParent()
        {
            return $this->parent;
        }

        protected function &internalValue()
        {
            return $this->parent->internalValue()[$this->data];
        }
    });
})();