<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class BooleanFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class BooleanFilterType extends BaseFilterType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            'choice',
            array(
                'required' => false,
                'choices' => array('' => '', '1' => 'yes', '0' => 'no'),
                'translation_domain' => 'sps',
                'attr' => array(
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-boolean-field',
                    'data-index' => $options['level'],
                ),
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'condition_operator_hidden' => 'eq',
                'condition_operators' => ConditionOperator::eqNotEq(),
                'choices' => array(),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_boolean_filter_type';
    }
}