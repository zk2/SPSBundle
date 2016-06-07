<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;
use Zk2\SPSBundle\Form\Type\DateRangeBootstrapType;

/**
 * Class DateRangeFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class DateRangeFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name', DateRangeBootstrapType::class, array(
            'required' => false,
            'attr' => array(
                'readonly' => 'readonly',
                'class' => 'zk2-sps-filter-field zk2-sps-filter-date-field',
                'data-index' => $options['level'],
            ),
            'label' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'condition_operators' => ConditionOperator::between(),
            'choices' => array(),
            'model_timezone' => date_default_timezone_get(),
            'view_timezone' => date_default_timezone_get(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_date_range_filter_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}