<?php
namespace Zk2\SPSBundle\Model;

/**
 * Class FilterField
 * @package Zk2\SPSBundle\Model
 */
class FilterField
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
     * @var array
     */
    protected $default_attr = array(
        'only_one_main_field' => false,
        'not_used' => false,
    );

    /**
     * @param $fieldAlias
     * @param $fieldName
     * @param $fieldType
     * @param array $attr
     */
    public function __construct($fieldAlias, $fieldName, $fieldType, array $attr)
    {
        $this->fieldAlias = $fieldAlias;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
        $this->attr = array_merge($this->default_attr, $attr);
        if('boolean' == $this->fieldType){
            $this->attr['only_one_main_field'] = true;
        }
    }

    /**
     * @return bool|null
     */
    public function getQuantity()
    {
        return $this->getAttr('quantity', null, 1);
    }

    /**
     * @param $name
     * @param null $subname
     * @param null $default
     * @return bool|null
     */
    public function getAttr($name, $subname = null, $default = null)
    {
        if ($subname) {
            return isset($this->attr[$name]) and isset($this->attr[$name][$subname]) ? $this->attr[$name][$subname] : $default;
        }

        return isset($this->attr[$name]) ? $this->attr[$name] : $default;
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
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(array(
            'sps_field_name' => $this->fieldName,
            'sps_field_alias' => $this->fieldAlias,
            'sps_field_type' => $this->fieldType,
        ), $this->attr);
    }
}
