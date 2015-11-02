<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class TextFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class TextFilterType extends BaseFilterType
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

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
        return 'zk2_sps_'.$this->type.'_filter_type';
    }
}