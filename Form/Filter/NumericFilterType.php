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
use Symfony\Component\Validator\Constraints\Type;
use Zk2\SpsBundle\Utils\ComparisonOperator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class NumericFilterType
 */
class NumericFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'name',
            TextType::class,
            [
                'required'    => false,
                'constraints' => [
                    new Type(
                        [
                            'type'    => "numeric",
                            'message' => "The value {{ value }} is not a valid {{ type }}.",
                        ]
                    ),
                ],
                'attr'        => [
                    'class'      => 'zk2-sps-filter-field zk2-sps-filter-numeric-field',
                    'data-index' => $options['level'],
                ],
                'label'       => false,
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
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_numeric_filter_type';
    }
}
