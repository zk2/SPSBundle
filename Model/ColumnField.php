<?php
namespace Zk2\SPSBundle\Model;

/**
 * Class ColumnField
 * @package Zk2\SPSBundle\Model
 */
class ColumnField
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $attr = array();

    /**
     * @var array
     */
    protected $default_attr = array(
        'sort' => true,
        'autosum' => null,
    );

    /**
     * @param $alias
     * @param $field
     * @param $type
     * @param array $attr
     */
    public function __construct($alias, $field, $type, array $attr)
    {
        $this->alias = $alias;
        $this->field = $field;
        $this->type = $type;
        $this->attr = array_merge($this->default_attr, $attr);
        if($this->type != 'numeric'){
            $this->attr['autosum'] = null;
        }
    }

    /**
     * @return array
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return array
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getLabel()
    {
        return $this->getAttr('label');
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

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttr('method', null, $this->field);
    }

    /**
     * @return string
     */
    public function getAliasDotName()
    {
        return ('noalias' == $this->alias ? null : $this->alias.'.').$this->field;
    }

    /**
     * getSortAlias
     * 
     * @return string
     */
    public function getSortAlias()
    {
        if($sa = $this->getAttr('sort_alias')){
            return $sa;
        }
        return $this->getAliasDotName();
    }
}
