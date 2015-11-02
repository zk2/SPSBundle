<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class DateFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class DateFilterType extends BaseFilterType
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
            'zk2_sps_date_bootstrap_type',
            array(
                'required' => false,
                'constraints' => array(
                    new Date(),
                ),
                'attr' => array(
                    'readonly' => 'readonly',
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-date-field',
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
                'condition_operators' => ConditionOperator::fullInt(),
                'choices' => array(),
                'model_timezone' => date_default_timezone_get(),
                'view_timezone' => date_default_timezone_get(),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_date_filter_type';
    }
}