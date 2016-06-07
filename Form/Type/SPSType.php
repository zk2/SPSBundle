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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['array_fields'] as $field) {
            if (!$field instanceof FilterField) {
                throw new InvalidArgumentException('SPSFilterType::__construct: Field must be instanceof FilterField');
            }
            for ($i = 0; $i < $field->getQuantity(); $i++) {
                $builder->add(
                    sprintf("%s__%s__%u", $field->getAlias(), $field->getField(), $i),
                    $field->getFormClass(), //sprintf("zk2_sps_%s_filter_type", $field->getType()),
                    array_merge($field->getAttributes(), array('level' => $i))
                );
                if ($field->getAttr('single_field')) {
                    break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'array_fields' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_builder_filter_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}