<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Zk2\SPSBundle\Utils\ConditionOperator;
use Zk2\SPSBundle\Form\Type\DateBootstrapType;

/**
 * Class DateFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class DateFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name', DateBootstrapType::class, array(
            'required' => false,
            'constraints' => array(new Date(),),
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
            'condition_operators' => ConditionOperator::fullInt(),
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
        return 'zk2_sps_date_filter_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}