<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class BaseFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
abstract class BaseFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['level']) {
            $builder->add('condition_pattern', ChoiceType::class, array(
                'choices' => array('condition_pattern.OR' => 'OR', 'condition_pattern.AND' => 'AND',),
                'choice_translation_domain' => 'sps',
                'attr' => array('class' => 'zk2-sps-filter-condition-pattern',),
                'label' => false,
            ));
        }

        if (!$options['single_field']) {
            $builder->add('condition_operator', ChoiceType::class, array(
                'choices' => ConditionOperator::get($options['condition_operators']),
                'choice_translation_domain' => 'sps',
                'attr' => array('class' => 'zk2-sps-filter-condition-operator',),
                'label' => false,
            ));
        } else {
            $builder->add('condition_operator', HiddenType::class, array(
                'data' => ConditionOperator::getMask($options['condition_operator_hidden']),
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'quantity' => 1,
                'condition_operators' => ConditionOperator::full(),
                'condition_operator_hidden' => ConditionOperator::EQ,
                'level' => 0,
                'not_used' => false,
                'single_field' => false,
                'sps_field_name' => null,
                'sps_field_alias' => null,
                'sps_field_type' => null,
            )
        );
    }

    /**
     * < 2.8
     */
    public function getName(){}
}