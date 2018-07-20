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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * class DateBootstrapType
 *
 * Implements the widget type "date" in form template
 * https://github.com/eternicode/bootstrap-datepicker
 *
 */
abstract class AbstractDateBootstrapType extends AbstractType
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale = 'en')
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** @noinspection SpellCheckingInspection */
        $defaults = [
            "autoclose"            => "true",
            "calendarWeeks"        => "false",
            "clearBtn"             => "true",
            "toggleActive"         => "false",
            "daysOfWeekDisabled"   => "[]",
            "datesDisabled"        => "[]",
            "endDate"              => "Infinity",
            "forceParse"           => "true",
            "format"               => "'yyyy-mm-dd'",
            "keyboardNavigation"   => "true",
            "language"             => "'".$this->locale."'",
            "minViewMode"          => "0",
            "multidate"            => "false",
            "multidateSeparator"   => "','",
            "orientation"          => "'bottom'",
            "rtl"                  => "false",
            "startDate"            => "-Infinity",
            "startView"            => "0",
            "todayBtn"             => "true",
            "todayHighlight"       => "true",
            "weekStart"            => "1",
            "disableTouchKeyboard" => "false",
            "enableOnReadonly"     => "true",
            "immediateUpdates"     => "false",
        ];

        $resolver->setDefaults(
            [
                'widget'         => 'single_text',
                'SpsDateSetting' => $defaults,
            ]
        );

        $resolver->setNormalizer(
            'SpsDateSetting',
            function (/** @noinspection PhpUnusedParameterInspection */Options $options, $configs) use ($defaults) {
                return array_merge($defaults, $configs);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace(
            $view->vars,
            ['SpsDateSetting' => $options['SpsDateSetting']]
        );
    }
}
