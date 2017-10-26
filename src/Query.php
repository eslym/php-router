<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/10/17
 * Time: 2:17 PM
 */

namespace onepeople\router;


use onepeople\utils\Compound;

/**
 * Class Query
 * @package onepeople\router
 * @method Query offsetGet($offset)
 */
class Query extends Compound
{
    /**
     * @return string
     */
    public static function proxyClass()
    {
        return QueryProxy::class;
    }

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
        $query->__data = $data;
        $query->__present = true;
        return $query;
    }

    /**
     * @return string
     */
    public function build()
    {
        return http_build_query($this->internalValue());
    }
}