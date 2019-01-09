<?php
/**
 * This file is part of the SpsBundle.
 *
 * (c) Evgeniy Budanov <budanov.ua@gmail.comm> 2017.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Zk2\SpsBundle\Form\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SpsBundle\Utils\ComparisonOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ChoiceFilterType
 */
class ChoiceFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            ChoiceType::class,
            [
                'required' => false,
                'choices' => $options['choices'],
                'attr' => [
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-choice-field',
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
                'comparison_operators' => ComparisonOperator::eqNotEq(),
                'choices' => [],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_choice_filter_type';
    }
}
