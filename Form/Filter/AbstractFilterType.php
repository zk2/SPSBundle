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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\SpsBundle\Utils\ComparisonOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class AbstractFilterType
 */
abstract class AbstractFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['level']) {
            $builder->add(
                'boolean_operator',
                ChoiceType::class,
                [
                    'choices'                   => ['boolean_operator.OR' => 'OR', 'boolean_operator.AND' => 'AND'],
                    'choice_translation_domain' => 'sps',
                    'attr'                      => ['class' => 'zk2-sps-filter-boolean-operator'],
                    'label'                     => false,
                ]
            );
        }

        if (!$options['comparison_operator_hidden']) {
            $builder->add(
                'comparison_operator',
                ChoiceType::class,
                [
                    'choices'                   => $options['comparison_operators'],
                    'choice_translation_domain' => 'sps',
                    'attr'                      => ['class' => 'zk2-sps-filter-comparison-operator'],
                    'label'                     => false,
                ]
            );
        } else {
            $builder->add(
                'comparison_operator',
                HiddenType::class,
                [
                    'data' => $options['comparison_operator_hidden'],
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'error_bubbling'             => false,
                'quantity'                   => 1,
                'comparison_operators'       => ComparisonOperator::full(),
                'comparison_operator_hidden' => null,
                'level'                      => 0,
                'not_used'                   => false,
                'sps_filter_name'            => null,
                'sps_filter_type'            => null,
                'sps_filter_field'           => null,
                'sps_filter_function'        => null,
                'function'                   => [],
            ]
        );
    }
}
