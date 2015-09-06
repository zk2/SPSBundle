<?php

namespace Zk2\SPSBundle\Model;

/**
 * The class contains static methods,
 * Returning SQL operator from mask
 */
class ConditionOperator
{
    public static $operators = array(
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

    public static function full()
    {
        return array_keys(self::$operators);
    }

    public static function eqNotEq()
    {
        return array('eq', 'not_eq');
    }

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

    public static function fullInt()
    {
        return array('eq', 'not_eq', 'less', 'less_eq', 'more', 'more_eq', 'is_empty', 'is_not_empty');
    }

    /**
     * Retruns an array of available conditions patterns.
     *
     * @return array
     */
    static public function get(array $condition_operators)
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
            $res[$tmp[0]] = $tmp[1];
            reset(self::$operators[$operator]);
        }

        return $res;
    }

    /**
     * validate condition_operator
     *
     * @return boolean
     */
    static public function validate(array $condition_operators)
    {
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
    static public function getValue($key)
    {
        if (!self::validate(array($key))) {
            throw new \InvalidArgumentException(
                sprintf("Key %s not exists in keys %s", $key, implode(' , ', array_keys(self::$operators)))
            );
        }

        return key(self::$operators[$key]);
    }

    /**
     * Retruns an array of available conditions patterns.
     *
     * @return string
     */
    static public function get2($condition_operator)
    {
        switch ($condition_operator) {
            case "%s"     :
                return "='|||'";
            case "x%s"    :
                return "<>'|||'";
            case "xx%s"   :
                return ">'|||'";
            case "xxx%s"  :
                return "<'|||'";
            case "xxxx%s" :
                return ">='|||'";
            case "xxxxx%s":
                return "<='|||'";
            case "%%%s%%" :
                return "LIKE '%|||%'";
            case "%s%%"   :
                return "LIKE '|||%'";
            case "%%%s"   :
                return "LIKE '%|||'";
            case "x%%%s%%":
                return "NOT LIKE '%|||%'";
            case "x%s%%"  :
                return "NOT LIKE '|||%'";
            case "x%%%s"  :
                return "NOT LIKE '%|||'";
        }

        return null;
    }

}
