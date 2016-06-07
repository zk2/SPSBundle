<?php
namespace Zk2\SPSBundle\Model;

/**
 * Abstract Class AbstractField
 * @package Zk2\SPSBundle\Model
 */
abstract class AbstractField
{
    /**
     * @var string
     */
    protected $fieldAlias;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @var array
     */
    protected $attr = array();

    /**
     * @param $fieldAlias
     * @param $fieldName
     * @param $fieldType
     */
    public function __construct($fieldAlias, $fieldName, $fieldType)
    {
        $this->fieldAlias = $fieldAlias;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->fieldAlias;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->fieldType;
    }

    /**
     * @param $name
     * @param null $subname
     * @param null $default
     * @return null
     */
    public function getAttr($name, $subname = null, $default = null)
    {
        if ($subname) {
            return (isset($this->attr[$name]) and isset($this->attr[$name][$subname])) ? $this->attr[$name][$subname] : $default;
        }

        return isset($this->attr[$name]) ? $this->attr[$name] : $default;
    }
}
