<?php

namespace Zk2\SPSBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use Zk2\SPSBundle\Utils\ConditionOperator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class NumericFilterType
 * @package Zk2\SPSBundle\Form\Filter
 */
class NumericFilterType extends BaseFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name', TextType::class, array(
            'required' => false,
            'constraints' => array(
                new Type(array(
                    'type' => "numeric",
                    'message' => "The value {{ value }} is not a valid {{ type }}.",
                )),
            ),
            'attr' => array(
                'class' => 'zk2-sps-filter-field zk2-sps-filter-numeric-field',
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
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_numeric_filter_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}