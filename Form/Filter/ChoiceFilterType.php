<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SPSBundle\Utils\ConditionOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ChoiceFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class ChoiceFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name', ChoiceType::class, array(
            'required' => false,
            'choices' => $options['choices'],
            'attr' => array(
                'class' => 'zk2-sps-filter-field zk2-sps-filter-choice-field',
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
            'condition_operators' => ConditionOperator::eqNotEq(),
            'choices' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_choice_filter_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}