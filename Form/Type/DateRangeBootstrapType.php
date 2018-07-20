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

namespace Zk2\SpsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Zk2\SpsBundle\Form\DataTransformer\DateRangeToArrayTransformer;

/**
 * class DateRangeBootstrapType
 *
 * Implements the widget type "date" in form template
 * https://github.com/eternicode/bootstrap-datepicker
 *
 */
class DateRangeBootstrapType extends AbstractDateBootstrapType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $baseOptions = [
            'widget'   => 'single_text',
            'required' => false,
            'label'    => false,
            'attr'     => ['class' => 'zk2-sps-filter-field zk2-sps-filter-date-range'],
        ];

        $builder
            ->add('start', DateType::class, $baseOptions)
            ->add('end', DateType::class, $baseOptions);

        $builder->addViewTransformer(new DateRangeToArrayTransformer(), true);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($options) {
                $data = $event->getData();
                $form = $event->getForm();
                if (!empty($data['start']) and empty($data['end'])) {
                    $form->get('end')->addError(new FormError('Invalid date end'));
                } elseif (empty($data['start']) and !empty($data['end'])) {
                    $form->get('start')->addError(new FormError('Invalid date from'));
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_date_range_bootstrap_type';
    }
}
