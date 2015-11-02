<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class ChoiceFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class ChoiceFilterType extends BaseFilterType
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
                'choices' => $options['choices'],
                'attr' => array(
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-choice-field',
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
        return 'zk2_sps_choice_filter_type';
    }
}