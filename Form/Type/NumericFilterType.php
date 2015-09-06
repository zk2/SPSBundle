<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use Zk2\SPSBundle\Model\ConditionOperator;

/**
 * Class NumericFilterType
 * @package Zk2\SPSBundle\Form\Type
 */
class NumericFilterType extends BaseFilterType
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
            null,
            array(
                'required' => false,
                'constraints' => array(
                    new Type(
                        array(
                            'type' => "numeric",
                            'message' => "The value {{ value }} is not a valid {{ type }}.",
                        )
                    ),
                ),
                'attr' => array(
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-numeric-field',
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
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_numeric_filter_type';
    }
}