<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class BaseFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
abstract class BaseFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['level']) {
            $builder->add(
                'condition_pattern',
                'choice',
                array(
                    'choices' => array(
                        'OR' => 'OR',
                        'AND' => 'AND',
                    ),
                    'attr' => array(
                        'class' => 'zk2-sps-filter-condition-pattern',
                    ),
                )
            );
        }

        if (!$options['only_one_main_field']) {
            $builder->add(
                'condition_operator',
                'choice',
                array(
                    'choices' => ConditionOperator::get($options['condition_operators']),
                    'attr' => array(
                        'class' => 'zk2-sps-filter-condition-operator',
                    ),
                )
            );
        } else {
            $builder->add(
                'condition_operator',
                'hidden',
                array(
                    'data' => ConditionOperator::getValue($options['condition_operator_hidden']),
                )
            );
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
                'quantity' => 1,
                'condition_operators' => ConditionOperator::full(),
                'condition_operator_hidden' => 'eq',
                'level' => 0,
                'not_used' => false,
                'only_one_main_field' => false,
                'sps_field_name' => null,
                'sps_field_alias' => null,
                'sps_field_type' => null,
            )
        );
    }

    /**
     * @return string
     */
    abstract public function getName();
}