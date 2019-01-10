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

use Zk2\SpsBundle\Exceptions\SpsException;

/**
 * Class SpsFilterField
 */
class SpsFilterField
{
    /**
     * @var string
     */
    private $filterName;

    /**
     * @var string
     */
    private $filterType;

    /**
     * @var array
     */
    private $attr = [];

    /**
     * @var array
     */
    protected $filterTypes = [
        'string',
        'numeric',
        'boolean',
        'date',
        'dateRange',
        'choice',
    ];

    /**
     * @var array
     */
    protected $defaultAttr = [
        'not_used' => false,
    ];

    /**
     * @param string $filterName
     * @param string $filterType
     * @param array  $attr
     *
     * @throws SpsException
     */
    public function __construct($filterName, $filterType, array $attr = [])
    {
        if (!in_array($filterType, $this->filterTypes)) {
            throw new SpsException(
                sprintf("Filter's type \"%s\" is not valid. Use %s", $filterType, implode(' or ', $this->filterTypes))
            );
        }
        if (isset($attr['sql_function'])) {
            $attr['sps_filter_sql_function'] = $attr['sql_function'];
            unset($attr['sql_function']);
        }
        if (isset($attr['php_function'])) {
            $attr['sps_filter_php_function'] = $attr['php_function'];
            unset($attr['php_function']);
        }
        $this->filterName = $filterName;
        $this->filterType = $filterType;
        $this->attr = array_merge($this->defaultAttr, $attr);
        if ('boolean' === $this->filterType) {
            $this->attr['quantity'] = 1;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->filterName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->filterType;
    }

    /**
     * @return string
     */
    public function getFormClass()
    {
        return sprintf("Zk2\\SpsBundle\\Form\\Filter\\%sFilterType", ucfirst($this->filterType));
    }

    /**
     * @return string
     */
    public function getNameForFormClass()
    {
        return str_replace(['.', ','], '_', $this->filterName);
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->getAttr('quantity', null, 1);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(
            [
                'sps_filter_name'  => $this->getNameForFormClass(),
                'sps_filter_field' => $this->filterName,
                'sps_filter_type'  => $this->filterType,
            ],
            $this->attr
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getAttr('label') ?: $this->humanize($this->filterName);
    }

    /**
     * @param string      $name
     * @param string|null $subname
     * @param string|null $default
     *
     * @return string|null
     */
    public function getAttr($name, $subname = null, $default = null)
    {
        if ($subname) {
            return (isset($this->attr[$name]) and isset($this->attr[$name][$subname]))
                ? $this->attr[$name][$subname]
                : $default;
        }

        return isset($this->attr[$name]) ? $this->attr[$name] : $default;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function humanize($text)
    {
        return ucwords(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s\.]+/'], ['_$1', ' '], $text))));
    }
}
