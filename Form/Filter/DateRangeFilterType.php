<?php

namespace Zk2\SpsBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SpsBundle\Utils\ComparisonOperator;
use Zk2\SpsBundle\Form\Type\DateRangeBootstrapType;

/**
 * Class DateRangeFilterType
 */
class DateRangeFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            DateRangeBootstrapType::class,
            [
                'required' => false,
                'attr' => [
                    'readonly' => 'readonly',
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-date-field',
                    'data-index' => $options['level'],
                ],
                'label' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'comparison_operators' => ComparisonOperator::between(),
                'choices' => [],
                'model_timezone' => date_default_timezone_get(),
                'view_timezone' => date_default_timezone_get(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_date_range_filter_type';
    }
}