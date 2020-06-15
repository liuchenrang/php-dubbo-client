<?php
/**
 * Idiot
 *  - Dubbo Client in Zookeeper.
 *
 * Licensed under the Massachusetts Institute of Technology
 *
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author   Lorne Wang < post@lorne.wang >
 * @link     https://github.com/lornewang/idiot
 */

namespace Idiot;

use Icecave\Flax\UniversalObject;
use Idiot\Utils\NamingUniversalObject;
use stdClass;

class Type
{
    const SHORT = 1;
    const INT = 2;
    const INTEGER = 2;
    const LONG = 3;
    const FLOAT = 4;
    const DOUBLE = 5;
    const STRING = 6;
    const BOOL = 7;
    const BOOLEAN = 7;

    public $name;
    public $value;
    public $type;

    public function __construct($type, $value, $name = null)
    {
        $this->type = $type;
        if (in_array($type, [self::SHORT, self::INT, self::INTEGER, self::LONG])) {
            $this->value = $value + 0;
        } elseif (in_array($type, [self::DOUBLE, self::FLOAT])) {
            $this->value = $value + 0.0;
        } elseif (in_array($type, [self::BOOL, self::BOOLEAN])) {
            $this->value = boolval($value);
        }else{
            $this->value  = $value;
        }
        if ($name) {
            $this->name = $name;
        }
    }

    /**
     * Short type
     *
     * @param integer $value
     * @param string $name
     * @return Type
     */
    public static function short($value, $name = "")
    {
        return new self(self::SHORT, $value, $name);
    }

    /**
     * Int type
     *
     * @param integer $value
     * @return Type
     */
    public static function int($value, $name = "")
    {
        return new self(self::INT, $value, $name);
    }

    /**
     * Integer type
     *
     * @param integer $value
     * @return Type
     */
    public static function integer($value, $name = "")
    {
        return new self(self::INTEGER, $value, $name);
    }

    /**
     * Long type
     *
     * @param integer $value
     * @return Type
     */
    public static function long($value, $name = "")
    {
        return new self(self::LONG, $value, $name);
    }

    /**
     * Float type
     *
     * @param integer $value
     * @return Type
     */
    public static function float($value, $name = "")
    {
        return new self(self::FLOAT, $value, $name);
    }

    /**
     * Double type
     *
     * @param integer $value
     * @return Type
     */
    public static function double($value, $name = "")
    {
        return new self(self::DOUBLE, $value, $name);
    }

    /**
     * String type
     *
     * @param string $value
     * @return Type
     */
    public static function string($value, $name = "")
    {
        return new self(self::STRING, $value, $name);
    }

    /**
     * Bool type
     *
     * @param boolean $value
     * @return Type
     */
    public static function bool($value, $name = "")
    {
        return new self(self::BOOL, $value, $name);
    }

    /**
     * Boolean type
     *
     * @param boolean $value
     * @return Type
     */
    public static function boolean($value, $name = "")
    {
        return new self(self::BOOLEAN, $value, $name);
    }

    /**
     * Object type
     *
     * @param integer $value
     * @return Object
     */
    public static function object($class, $properties, $name = "")
    {
        $std = new stdClass;
        foreach ($properties as $key => $value) {
            $std->$key = ($value instanceof Type) ? $value->value : $value;
        }
        return new NamingUniversalObject($class, $std, $name);
    }

}