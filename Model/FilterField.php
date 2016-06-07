<?php
namespace Zk2\SPSBundle\Model;

/**
 * Class FilterField
 * @package Zk2\SPSBundle\Model
 */
class FilterField extends AbstractField
{
    /**
     * @var array
     */
    protected $defaultAttr = array(
        'single_field' => false,
        'not_used' => false,
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
        if('boolean' == $this->fieldType){
            $this->attr['single_field'] = true;
        }
    }

    /**
     * @return string
     */
    public function getFormClass()
    {
        return sprintf("Zk2\\SPSBundle\\Form\\Filter\\%sFilterType", ucfirst($this->fieldType));
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        if ($this->attr['single_field']) return 1;
        return $this->getAttr('quantity', null, 1);
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
