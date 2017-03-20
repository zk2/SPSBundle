<?php

namespace Zk2\SpsBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Zk2\SpsBundle\Utils\ComparisonOperator;
use Zk2\SpsBundle\Form\Type\DateBootstrapType;

/**
 * Class DateFilterType
 */
class DateFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            DateBootstrapType::class,
            [
                'required' => false,
                'constraints' => [new Date(),],
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
                'comparison_operators' => ComparisonOperator::fullInt(),
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
        return 'zk2_sps_date_filter_type';
    }
}