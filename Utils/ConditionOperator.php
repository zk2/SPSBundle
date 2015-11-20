<?php

namespace Zk2\SPSBundle\Utils;

/**
 * The class contains static methods,
 * Returning SQL operator from mask
 */
/**
 * Class ConditionOperator
 * @package Zk2\SPSBundle\Utils
 */
class ConditionOperator
{
    /**
     * @var array
     */
    private static $operators = array(
        'eq' => array('%s' => '='),
        'not_eq' => array('x%s' => '!=',),
        '_like_' => array('%%%s%%' => '%LIKE%'),
        'like_' => array('%s%%' => 'LIKE%'),
        '_like' => array('%%%s' => '%LIKE'),
        'not__like_' => array('x%%%s%%' => 'NOT %LIKE%'),
        'not_like_' => array('x%s%%' => 'NOT LIKE%'),
        'not__like' => array('x%%%s' => 'NOT %LIKE'),
        'less' => array('xxx%s' => '<',),
        'less_eq' => array('xxxxx%s' => '<='),
        'more' => array('xx%s' => '>'),
        'more_eq' => array('xxxx%s' => '>='),
        'is_empty' => array('IS NULL' => 'IS EMPTY'),
        'is_not_empty' => array('IS NOT NULL' => 'IS NOT EMPTY'),
        'yes_no' => array('TRUE_FALSE' => '='),
    );

    /**
     * validate condition_operator
     *
     * @return boolean
     */
    private static function validate($condition_operators)
    {
        if(!is_array($condition_operators)){
            $condition_operators = array($condition_operators);
        }
        foreach ($condition_operators as $operator) {
            if (!isset(self::$operators[$operator])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retruns an array of available conditions patterns.
     *
     * @return array
     */
    public static function get(array $condition_operators)
    {
        if (!self::validate($condition_operators)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Keys %s not mapping in keys %s",
                    implode(',', $condition_operators),
                    implode(',', array_keys(self::$operators))
                )
            );
        }
        $res = array();
        foreach ($condition_operators as $operator) {
            $tmp = each(self::$operators[$operator]);
            $res[$tmp['key']] = $tmp['value'];
            reset(self::$operators[$operator]);
        }

        return $res;
    }

    /**
     * Retrun operator (=, <>, etc...)
     *
     * @return string
     */
    public static function getOperator($signature)
    {
        if(!$signature) return null;
        foreach(self::$operators as $operator){
            $tmp = each($operator);
            reset($operator);
            if ($tmp['key'] == $signature){
                return str_replace("%", "", $tmp['value']);
            }
        }
        throw new \InvalidArgumentException('Bad signature "'.$signature.'"');
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
        return array('eq', 'not_eq');
    }

    /**
     * @return array
     */
    public static function fullText()
    {
        return array(
            '_like_',
            'eq',
            'not_eq',
            'like_',
            '_like',
            'not__like_',
            'not_like_',
            'not__like',
            'is_empty',
            'is_not_empty',
        );
    }

    /**
     * @return array
     */
    public static function fullInt()
    {
        return array('eq', 'not_eq', 'less', 'less_eq', 'more', 'more_eq', 'is_empty', 'is_not_empty');
    }

    /**
     * @return array
     */
    public static function mediumText()
    {
        return array(
            '_like_',
            'eq',
            'not_eq',
            'not__like_',
            'is_empty',
            'is_not_empty',
        );
    }

    /**
     * @return array
     */
    public static function mediumInt()
    {
        return array('eq', 'not_eq', 'less', 'more', 'is_empty', 'is_not_empty');
    }

    /**
     * @return array
     */
    public static function smallText()
    {
        return array(
            '_like_',
            'eq',
            'not_eq',
            'not__like_',
        );
    }

    /**
     * @return array
     */
    public static function smallInt()
    {
        return array('eq', 'not_eq', 'less', 'more');
    }

    /**
     * Retruns an array of available conditions patterns.
     *
     * @return array
     */
    public static function getValue($key)
    {
        if (!self::validate($key)) {
            throw new \InvalidArgumentException(
                sprintf("Key %s not exists in keys %s", $key, implode(' , ', array_keys(self::$operators)))
            );
        }

        return key(self::$operators[$key]);
    }
}
