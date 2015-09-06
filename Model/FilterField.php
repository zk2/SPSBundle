<?php
namespace Zk2\SPSBundle\Model;

/**
 * Class FilterField
 */
class FilterField
{
    protected
        $alias,
        $field,
        $type,
        $attr = array();

    /**
     * Constructor
     *
     * @param array $array
     */
    public function __construct($alias, $field, $type, array $attr)
    {
        $this->alias = $alias;
        $this->field = $field;
        $this->type = $type;
        $this->attr = $attr;
    }

    public function getQuantity()
    {
        return $this->getAttr('quantity', null, 1);
    }

    public function getAttr($name, $subname = null, $default = null)
    {
        if ($subname) {
            return isset($this->attr[$name]) and isset($this->attr[$name][$subname]) ? $this->attr[$name][$subname] : $default;
        }

        return isset($this->attr[$name]) ? $this->attr[$name] : $default;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAttributes()
    {
        return $this->attr;
    }
}
