<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Model\FilterField;
use Zk2\SPSBundle\Exceptions\InvalidArgumentException;

/**
 * Class SPSType
 * @package Zk2\SPSBundle\Form\Type
 */
class SPSType extends AbstractType
{
    /**
     * @var array
     */
    protected $array_fields;

    /**
     * @param array $array_fields
     * @throws \Exception
     */
    public function __construct(array $array_fields)
    {
        foreach ($array_fields as $field) {
            if (!$field instanceof FilterField) {
                throw new InvalidArgumentException('SPSFilterType::__construct: Field must be instanceof FilterField');
            }
        }
        $this->array_fields = $array_fields;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->array_fields as $field) {
            for ($i = 0; $i < $field->getQuantity(); $i++) {
                $builder->add(
                    sprintf("%s__%s%u", $field->getAlias(), $field->getField(), $i),
                    sprintf("zk2_sps_%s_filter_type", $field->getType()),
                    array_merge(
                        $field->getAttributes(),
                        array('level' => $i)
                    )
                );
                if ($field->getAttr('only_one_main_field')) {
                    break;
                }
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_builder_filter_type';
    }
}