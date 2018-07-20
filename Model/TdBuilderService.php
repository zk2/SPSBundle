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

namespace Zk2\SpsBundle\Model;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;
use Zk2\SpsBundle\Exceptions\SpsException;

/**
 * Class TdBuilderService
 */
class TdBuilderService implements TdBuilderInterface
{
    /**
     * @var string
     */
    protected $td;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $pathToWebDir;

    /**
     * @return array
     */
    public static function getInjections()
    {
        return ['setRouter' => 'router', 'setTranslator' => 'translator'];
    }

    /**
     * @param string $path
     */
    public function setFullWebPath($path)
    {
        $this->pathToWebDir = $path ? realpath($path) : null;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param SpsColumnField $column
     * @param array          $row
     *
     * @return string
     */
    public function getTd(SpsColumnField $column, array $row)
    {
        $this->td = $this->beginTd($column);
        $this->td .= $this->buildTd($column, $row);
        $this->td .= $this->endTd();

        return $this->td;
    }

    /**
     * @param array            $autosum ,
     * @param SpsColumnField[] $columns
     *
     * @return string
     *
     * @throws SpsException
     */
    public function getAutosum(array $autosum, array $columns)
    {
        $row = '';
        foreach ($columns as $column) {
            if (!$column instanceof SpsColumnField) {
                throw new SpsException(sprintf('Array of columns must consist of elements "%s', SpsColumnField::class));
            }
            $row .= sprintf(
                "%s%s%s",
                $this->beginTd($column, 'th'),
                $column->getAttr('autosum') ? $this->buildNumeric(
                    $column,
                    $autosum[$column->getAttr('autosum')]
                ) : null,
                $this->endTd('th')
            );
        }

        return $row;
    }

    /**
     * @param SpsColumnField $column
     * @param string         $type
     *
     * @return string
     */
    protected function beginTd(SpsColumnField $column, $type = 'td')
    {
        return sprintf(
            '<%s%s%s>',
            $type,
            $column->getAttr('class') ? sprintf(' class="%s"', $column->getAttr('class')) : null,
            $column->getAttr('style') ? sprintf(' style="%s"', $column->getAttr('style')) : null
        );
    }

    /**
     * @param SpsColumnField $column
     * @param array          $row
     *
     * @return string
     */
    protected function buildTd(SpsColumnField $column, array $row)
    {
        $value = isset($row[$column->getName()]) ? $row[$column->getName()] : null;

        if ('numeric' === $column->getType()) {
            $value = $this->buildNumeric($column, $value);
        } elseif ('datetime' === $column->getType() && $value) {
            $value = $this->buildDateTime($column, $value);
        } elseif ('boolean' === $column->getType()) {
            $value = $this->buildBoolean($column, $value);
        } elseif ('image' === $column->getType() && $value) {
            $value = $this->buildImage($column, $value);
        }

        if ($column->getAttr('choice_list')) {
            $value = $this->buildFromChoice($column, $value);
        }

        if ($column->getAttr('link')) {
            $value = $this->buildLink($column, $value, $row);
        }

        return $value;
    }

    /**
     * @param SpsColumnField $column
     * @param string|int     $value
     *
     * @return string
     *
     * @throws SpsException
     */
    protected function buildNumeric(SpsColumnField $column, $value)
    {
        $format = $column->getAttr('number_format');
        if ($format and !is_array($format)) {
            throw new SpsException('Number format must be array');
        }
        if ($format) {
            if (2 === count($format)) {
                $value = number_format($value, $format[0], $format[1]);
            } elseif (3 === count($format)) {
                $value = number_format($value, $format[0], $format[1], $format[2]);
            } else {
                throw new SpsException('This array format accepts either zero, two, or three items');
            }
        }

        return $value;
    }

    /**
     * @param SpsColumnField   $column
     * @param \DateTime|string $value
     *
     * @return string
     *
     * @throws SpsException
     */
    protected function buildDateTime(SpsColumnField $column, $value)
    {
        if (!$value instanceof \DateTime) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                throw new SpsException($e->getMessage());
            }
        }
        $dateFormat = $column->getAttr('format') ?: 'Y-m-d H:i:s';
        if ($dateTimezone = $column->getAttr('timezone')) {
            try {
                $tz = new \DateTimeZone($dateTimezone);
                $value->setTimezone($tz);
            } catch (\Exception $e) {
                throw new SpsException($e->getMessage());
            }
        }
        $value = $value->format($dateFormat);

        return $value;
    }

    /**
     * @param SpsColumnField $column
     * @param string         $value
     *
     * @return string
     */
    protected function buildBoolean(SpsColumnField $column, $value)
    {
        if ($column->getAttr('revers')) {
            $totalStatus = $value ? 'unchecked' : 'check';
        } else {
            $totalStatus = $value ? 'check' : 'unchecked';
        }
        if ('icon' === $column->getAttr('boolean_view')) {
            $value = sprintf('<i class="glyphicon glyphicon-%s"></i>', $totalStatus);
        } else {
            $value = 'unchecked' === $totalStatus
                ? $this->translator->trans('no', [], 'sps')
                : $this->translator->trans('yes', [], 'sps');
        }

        return $value;
    }

    /**
     * @param SpsColumnField $column
     * @param string         $value
     *
     * @return string
     */
    protected function buildImage(SpsColumnField $column, $value)
    {
        $default = ltrim($column->getAttr('image_by_default'), DIRECTORY_SEPARATOR);
        $value = ltrim($value, DIRECTORY_SEPARATOR);
        $imageWebPath = rtrim($column->getAttr('image_web_path'), DIRECTORY_SEPARATOR);

        if (!file_exists($this->pathToWebDir.DIRECTORY_SEPARATOR.$imageWebPath.DIRECTORY_SEPARATOR.$value)) {
            if ($default) {
                $value = $default;
            } else {
                $value = null;
            }
        }

        $value = sprintf(
            '<img src="%s" width="%s" title="%s" alt="%s"/>',
            ($value ? $imageWebPath.DIRECTORY_SEPARATOR.$value : null),
            $column->getAttr('image_width', null, 50),
            $column->getAttr('image_title', null, 'Icon'),
            $column->getAttr('image_alt', null, 'Icon')
        );

        return $value;
    }

    /**
     * @param SpsColumnField $column
     * @param string         $value
     *
     * @return string
     */
    protected function buildFromChoice(SpsColumnField $column, $value)
    {
        if ($choiceList = $column->getAttr('choice_list') and is_array($choiceList)) {
            $value = $choiceList[$value];
        }

        return $value;
    }

    /**
     * @param SpsColumnField $column
     * @param string         $value
     * @param array          $row
     *
     * @return string
     */
    protected function buildLink(SpsColumnField $column, $value, $row)
    {
        $linkRout = $column->getAttr('link', 'route');
        $linkRoutParams = $column->getAttr('link', 'route_params', []);
        $linkRoutParams = array_map(
            function ($param) use ($row, $column) {
                return isset($row[$column->getAliasAndDotOrNull().$param])
                    ? $row[$column->getAliasAndDotOrNull().$param]
                    : $param;
            },
            $linkRoutParams
        );
        $linkSessionKeyParams = $column->getSessionKeyParams();
        $onClick = $column->getAttr('link', 'on_click');
        $linkClass = $column->getAttr('link', 'link_class');
        $linkStyle = $column->getAttr('link', 'link_style', 'text-decoration:underline;');
        $linkText = $column->getAttr('link', 'text', $value) ?: 'Link';
        $href = $column->getAttr('link', 'link_javascript')
            ? 'javascript:void(0)'
            : $this->router->generate($linkRout, array_merge($linkRoutParams, $linkSessionKeyParams));
        $value = sprintf(
            '<a href="%s"%s%s%s%s>%s</a>',
            $href,
            ($onClick ? " onclick='".$onClick."'" : null),
            ($linkClass ? ' class="'.$linkClass.'"' : null),
            ($linkStyle ? ' style="'.$linkStyle.'"' : null),
            ($column->getAttr('link', 'link_javascript') ? " data-id='".json_encode($linkRoutParams)."'" : null),
            $linkText
        );

        return $value;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function endTd($type = 'td')
    {
        return sprintf("</%s>", $type);
    }
}
