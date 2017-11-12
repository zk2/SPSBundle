<?php

namespace Zk2\SpsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SpsBundle\Model\SpsFilterField;

/**
 * Class SpsType
 */
class SpsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SpsFilterField $field */
        foreach ($options['array_fields'] as $field) {
            for ($i = 0; $i < $field->getQuantity(); $i++) {
                $builder->add(
                    sprintf("%s__%u", $field->getNameForFormClass(), $i),
                    $field->getFormClass(),
                    array_merge($field->getAttributes(), ['level' => $i, 'label' => $i ? false : $field->getLabel()])
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'error_bubbling' => false,
                'csrf_protection' => false,
                'array_fields' => [],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_builder_filter_type';
    }
}