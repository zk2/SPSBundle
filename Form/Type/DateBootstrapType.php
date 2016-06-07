<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * class DateBootstrapType
 *
 * Implements the widget type "date" in form template
 * https://github.com/eternicode/bootstrap-datepicker
 *
 */
class DateBootstrapType extends AbstractDateBootstrapType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_sps_date_bootstrap_type';
    }

    /**
     * < 2.8
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}