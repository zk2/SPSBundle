<?php

namespace Zk2\SPSBundle\Utils;

use Zk2\SPSBundle\Exceptions\InvalidArgumentException;

/**
 * Class ConditionOperator
 * The class contains static methods,
 * Returning SQL operator from mask
 * @package Zk2\SPSBundle\Utils
 */
class ConditionOperator
{
    const EQ = 'eq';
    const NOT_EQ = 'not_eq';
    const _LIKE_ = '_like_';
    const LIKE_ = 'like_';
    const _LIKE = '_like';
    const NOT__LIKE_ = 'not__like_';
    const NOT_LIKE_ = 'not_like_';
    const NOT__LIKE = 'not__like';
    const LESS = 'less';
    const LESS_EQ = 'less_eq';
    const MORE = 'more';
    const MORE_EQ = 'more_eq';
    const IS_EMPTY = 'is_empty';
    const IS_NOT_EMPTY = 'is_not_empty';
    const BOOL_ = 'bool_';
    const BETWEEN = 'between';
    const NOT_BETWEEN = 'not_between';
    
    /**
     * @var array
     */
    private static $operators = array(
        self::EQ => array(
            'mask' => '%s',
            'value' => '=',
            'label' => 'operator.eq',
        ),
        self::NOT_EQ => array(
            'mask' => 'x%s',
            'value' => '!=',
            'label' => 'operator.not_eq',
        ),
        self::_LIKE_ => array(
            'mask' => '%%%s%%',
            'value' => 'LIKE',
            'label' => 'operator._like_',
        ),
        self::LIKE_ => array(
            'mask' => '%s%%',
            'value' => 'LIKE',
            'label' => 'operator.like_',
        ),
        self::_LIKE => array(
            'mask' => '%%%s',
            'value' => 'LIKE',
            'label' => 'operator._like',
        ),
        self::NOT__LIKE_ => array(
            'mask' => 'x%%%s%%',
            'value' => 'NOT LIKE',
            'label' => 'operator.not__like_',
        ),
        self::NOT_LIKE_ => array(
            'mask' => 'x%s%%',
            'value' => 'NOT LIKE',
            'label' => 'operator.not_like_',
        ),
        self::NOT__LIKE => array(
            'mask' => 'x%%%s',
            'value' => 'NOT LIKE',
            'label' => 'operator.not__like',
        ),
        self::LESS => array(
            'mask' => 'xxx%s',
            'value' => '<',
            'label' => 'operator.less',
        ),
        self::LESS_EQ => array(
            'mask' => 'xxxxx%s',
            'value' => '<=',
            'label' => 'operator.less_eq',
        ),
        self::MORE => array(
            'mask' => 'xx%s',
            'value' => '>',
            'label' => 'operator.more',
        ),
        self::MORE_EQ => array(
            'mask' => 'xxxx%s',
            'value' => '>=',
            'label' => 'operator.more_eq',
        ),
        self::IS_EMPTY => array(
            'mask' => 'IS NULL',
            'value' => 'IS NULL',
            'label' => 'operator.is_empty',
        ),
        self::IS_NOT_EMPTY => array(
            'mask' => 'IS NOT NULL',
            'value' => 'IS NOT NULL',
            'label' => 'operator.is_not_empty',
        ),
        self::BOOL_ => array(
            'mask' => 'xxxxxx%s',
            'value' => '=',
            'label' => 'operator.bool_',
        ),
        self::BETWEEN => array(
            'mask' => 'xxxxxxx%s',
            'value' => '=',
            'label' => 'operator.between',
        ),
        self::NOT_BETWEEN => array(
            'mask' => 'xxxxxxxx%s',
            'value' => '!=',
            'label' => 'operator.not_between',
        ),
    );

    /**
     * validate condition_operator
     *
     * @param $condition_operators
     * @return bool
     * @throws InvalidArgumentException
     */
    private static function validate($condition_operators)
    {
        if (!is_array($condition_operators)) {
            $condition_operators = array($condition_operators);
        }
        foreach ($condition_operators as $operator) {
            if (!isset(self::$operators[$operator])) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Keys \"%s\" not mapping in keys \"%s\"",
                        implode(',', $condition_operators),
                        implode(',', array_keys(self::$operators))
                    )
                );
            }
        }

        return true;
    }

    /**
     * Return an array of available conditions patterns.
     *
     * @param array $condition_operators
     * @return array
     * @throws InvalidArgumentException
     */
    public static function get(array $condition_operators)
    {
        self::validate($condition_operators);
        $operators = array();
        foreach ($condition_operators as $operator) {
            $operators[self::$operators[$operator]['label']] = self::$operators[$operator]['mask'];
        }

        return $operators;
    }

    /**
     * Return operator (=, <>, etc...)
     *
     * @param string $signature
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getOperator($signature)
    {
        foreach (self::$operators as $operator) {
            if ($operator['mask'] == $signature) {
                return $operator['value'];
            }
        }
        throw new \InvalidArgumentException('Bad signature "' . $signature . '"');
    }

    /**
     * Return an array of available conditions patterns.
     *
     * @param string $key
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getMask($key)
    {
        self::validate($key);

        return self::$operators[$key]['mask'];
    }

    /**
     * @return array
     */
    public static function full()
    {
        return array_keys(self::$operators);
    }

    /**
     * @return array
     */
    public static function eqNotEq()
    {
        return array(self::EQ, self::NOT_EQ);
    }

    /**
     * @return array
     */
    public static function fullText()
    {
        return array(
            self::_LIKE_,
            self::EQ,
            self::NOT_EQ,
            self::LIKE_,
            self::_LIKE,
            self::NOT__LIKE_,
            self::NOT_LIKE_,
            self::NOT__LIKE,
            self::IS_EMPTY,
            self::IS_NOT_EMPTY,
        );
    }

    /**
     * @return array
     */
    public static function fullInt()
    {
        return array(
            self::EQ,
            self::NOT_EQ,
            self::LESS,
            self::LESS_EQ,
            self::MORE,
            self::MORE_EQ,
            self::IS_EMPTY,
            self::IS_NOT_EMPTY,
        );
    }

    /**
     * @return array
     */
    public static function mediumText()
    {
        return array(
            self::_LIKE_,
            self::EQ,
            self::NOT_EQ,
            self::NOT__LIKE_,
            self::IS_EMPTY,
            self::IS_NOT_EMPTY,
        );
    }

    /**
     * @return array
     */
    public static function mediumInt()
    {
        return array(
            self::EQ,
            self::NOT_EQ,
            self::LESS,
            self::MORE,
            self::IS_EMPTY,
            self::IS_NOT_EMPTY,
        );
    }

    /**
     * @return array
     */
    public static function smallText()
    {
        return array(
            self::_LIKE_,
            self::EQ,
            self::NOT_EQ,
            self::NOT__LIKE_,
        );
    }

    /**
     * @return array
     */
    public static function smallInt()
    {
        return array(
            self::EQ,
            self::NOT_EQ,
            self::LESS,
            self::MORE,
        );
    }

    /**
     * @return array
     */
    public static function between()
    {
        return array(
            self::BETWEEN,
            self::NOT_BETWEEN,
        );
    }
}
