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
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class StringFilterType
 */
class StringFilterType extends AbstractFilterType
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
                'required' => false,
                'attr' => [
                    'class' => 'zk2-sps-filter-field zk2-sps-filter-text-field',
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
                'comparison_operators' => ComparisonOperator::fullText(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_string_filter_type';
    }
}
