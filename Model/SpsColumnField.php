<?php

namespace Zk2\SpsBundle\Model;

use Zk2\SpsBundle\Exceptions\SpsException;

class SpsColumnField
{
    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string
     */
    private $columnType;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    private $attr = [];

    /**
     * @var array
     */
    private $columnTypes = [
        'string',
        'numeric',
        'boolean',
        'datetime',
        'image',
    ];

    /**
     * @var string|null
     */
    private $sortType;

    /**
     * @var string|null
     */
    private $sessionKey;

    /**
     * @var array
     */
    protected $defaultAttr = [
        'sort' => true,
        'autosum' => null,
    ];

    /**
     * @param string $columnName
     * @param string $columnType
     * @param array $attr
     * @throws SpsException
     */
    public function __construct($columnName, $columnType, array $attr = [])
    {
        if (!in_array($columnType, $this->columnTypes)) {
            throw new SpsException(
                sprintf("Column's type \"%s\" is not valid. Use %s", $columnType, implode(' or ', $this->columnTypes))
            );
        }
        $this->columnName = $columnName;
        $this->columnType = $columnType;
        $arr = explode('.', $this->columnName);
        $this->alias = count($arr) === 2 ? $arr[0] : null;
        $this->field = count($arr) === 2 ? $arr[1] : $arr[0];
        $this->attr = array_merge($this->defaultAttr, $attr);
        if ($this->columnType != 'numeric') {
            $this->attr['autosum'] = null;
        }
        if ($this->columnType == 'boolean' and isset($this->attr['boolean_view']) and !$this->attr['boolean_view']) {
            $this->attr['boolean_view'] = 'icon';
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->columnName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->columnType;
    }

    /**
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (false === $this->getAttr('label')) {

            return null;
        }

        return $this->getAttr('label') ?: $this->humanize($this->field);
    }

    /**
     * @return mixed
     */
    public function getNumberFormat()
    {
        return $this->getAttr('number_format') ?: [0, '', ''];
    }

    /**
     * @param $name
     * @param string|null $subname
     * @param string|null $default
     * @return array|string|null
     */
    public function getAttr($name, $subname = null, $default = null)
    {
        if ($subname) {
            return
                (isset($this->attr[$name]) and isset($this->attr[$name][$subname]))
                    ? $this->attr[$name][$subname]
                    : $default;
        }

        return isset($this->attr[$name]) ? $this->attr[$name] : $default;
    }

    /**
     * @return null|string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * @param null|string $sortType
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;
    }

    /**
     * @return null|string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * @param null|string $sessionKey
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * @return array
     */
    public function getSessionKeyParams()
    {
        return $this->sessionKey ? [SpsService::SESSION_KEY_NAME => $this->sessionKey] : [];
    }

    /**
     * @return null|string
     */
    public function getAliasAndDotOrNull()
    {
        return $this->alias ? $this->alias.'.' : null;
    }

    /**
     * @param string $text
     * @return string
     */
    public function humanize($text)
    {
        return ucwords(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s\.]+/'], ['_$1', ' '], $text))));
    }
}
