<?php
namespace Zk2\SPSBundle\Model;

/**
 * Class ColumnField
 * @package Zk2\SPSBundle\Model
 */
class ColumnField extends AbstractField
{
    const NOALIAS = 'noalias';
    
    /**
     * @var array
     */
    protected $defaultAttr = array(
        'sort' => true,
        'autosum' => null,
        'type_view' => 'icon',
    );

    /**
     * @param string $fieldAlias
     * @param string $fieldName
     * @param string $fieldType
     * @param array $attr
     */
    public function __construct($fieldAlias, $fieldName, $fieldType, array $attr)
    {
        parent::__construct($fieldAlias, $fieldName, $fieldType);
        $this->attr = array_merge($this->defaultAttr, $attr);
        if ($this->fieldType != 'numeric') {
            $this->attr['autosum'] = null;
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getAttr('label');
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttr('method', null, $this->fieldName);
    }

    /**
     * @return string
     */
    public function getAliasDotName()
    {
        return (self::NOALIAS == $this->fieldAlias ? null : $this->fieldAlias . '.') . $this->fieldName;
    }

    /**
     * @return string
     */
    public function getSortAlias()
    {
        if ($sortAlias = $this->getAttr('sort_alias')) {
            return $sortAlias;
        }
        return $this->getAliasDotName();
    }
}
