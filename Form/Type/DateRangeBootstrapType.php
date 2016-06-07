<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Zk2\SPSBundle\Form\DataTransformer\DateRangeToArrayTransformer;

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
        $baseOptions = array(
            'widget' => 'single_text',
            'required' => false,
            'label' => false,
            'error_bubbling' => true,
            'attr' => array('class' => 'zk2-sps-filter-field zk2-sps-filter-date-range')
        );

        $builder
            ->add('start', DateType::class, $baseOptions)
            ->add('end', DateType::class, $baseOptions)
        ;

        $builder->addViewTransformer(new DateRangeToArrayTransformer(), true);
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

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}