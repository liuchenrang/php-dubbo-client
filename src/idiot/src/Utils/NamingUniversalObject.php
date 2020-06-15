<?php


namespace Idiot\Utils;


use Icecave\Flax\UniversalObject;

class NamingUniversalObject extends UniversalObject
{
    protected $className;
    protected $object;
    protected $name;

    /**
     * NamingUniversalObject constructor.
     * @param $className
     * @param $object
     * @param $name
     */
    public function __construct($className, $object, $name="")
    {
        $this->className = $className;
        $this->object = $object;
        $this->name = $name;
    }

    public function className()
    {
        return $this->className;
    }

    /**
     * Get the internal obejct.
     *
     * @return string The internal object.
     */
    public function object()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


}