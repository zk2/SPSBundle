<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Model\ConditionOperator;

/**
 * Class TextFilterType
 * @package Zk2\SPSBundle\Form\Type
 */
class TextFilterType extends BaseFilterType
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
                'attr' => array(
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-text-field',
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
                'condition_operator_hidden' => '_like_',
                'condition_operators' => ConditionOperator::fullText(),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_text_filter_type';
    }
}