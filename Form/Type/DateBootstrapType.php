<?php

namespace Zk2\SPSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * class DateBootstrapType
 *
 * Implements the widget type "date" in form template
 *
 */
class DateBootstrapType extends AbstractType
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @param $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array(
            "autoclose" => "true",
            "beforeShowDay" => "$.noop",
            "beforeShowMonth" => "$.noop",
            "calendarWeeks" => "false",
            "clearBtn" => "true",
            "toggleActive" => "false",
            "daysOfWeekDisabled" => "[]",
            "datesDisabled" => "[]",
            "endDate" => "Infinity",
            "forceParse" => "true",
            "format" => "'yyyy-mm-dd'",
            "keyboardNavigation" => "true",
            "language" => "'".$this->locale."'",
            "minViewMode" => "0",
            "multidate" => "false",
            "multidateSeparator" => "','",
            "orientation" => "'bottom'",
            "rtl" => "false",
            "startDate" => "-Infinity",
            "startView" => "0",
            "todayBtn" => "false",
            "todayHighlight" => "false",
            "weekStart" => "1",
            "disableTouchKeyboard" => "false",
            "enableOnReadonly" => "true",
            "immediateUpdates" => "false",
        );

        $resolver->setDefaults(
            array(
                'widget' => 'single_text',
                'SPSDateSetting' => $defaults,
            )
        );
        $resolver->setNormalizer(
            'SPSDateSetting',
            function (Options $options, $configs) use ($defaults) {
                return array_merge($defaults, $configs);
            }
        );
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace(
            $view->vars,
            array(
                'SPSDateSetting' => $options['SPSDateSetting'],
            )
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zk2_sps_date_bootstrap_type';
    }
}