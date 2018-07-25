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

namespace Zk2\SpsBundle\Utils;

use Zk2\SpsComponent\Condition\ConditionInterface;

/**
 * Class ComparisonOperator
 */
class ComparisonOperator
{
    const COMPARISON_LABELS = [
        ConditionInterface::TOKEN_CONTAINS              => 'operator.contains',
        ConditionInterface::TOKEN_BEGINS_WITH           => 'operator.begins_with',
        ConditionInterface::TOKEN_ENDS_WITH             => 'operator.ends_with',
        ConditionInterface::TOKEN_NOT_CONTAINS          => 'operator.not_contains',
        ConditionInterface::TOKEN_NOT_BEGINS_WITH       => 'operator.not_begins_with',
        ConditionInterface::TOKEN_NOT_ENDS_WITH         => 'operator.not_ends_with',
        ConditionInterface::TOKEN_EQUALS                => 'operator.eq',
        ConditionInterface::TOKEN_NOT_EQUALS            => 'operator.not_eq',
        ConditionInterface::TOKEN_GREATER_THAN          => 'operator.greater',
        ConditionInterface::TOKEN_GREATER_THAN_OR_EQUAL => 'operator.greater_eq',
        ConditionInterface::TOKEN_LESS_THAN             => 'operator.less',
        ConditionInterface::TOKEN_LESS_THAN_OR_EQUAL    => 'operator.less_eq',
        ConditionInterface::TOKEN_IS_NULL               => 'operator.is_empty',
        ConditionInterface::TOKEN_IS_NOT_NULL           => 'operator.is_not_empty',
        ConditionInterface::TOKEN_BETWEEN               => 'operator.between',
        ConditionInterface::TOKEN_NOT_BETWEEN           => 'operator.not_between',
    ];

    /**
     * @return array
     */
    public static function full()
    {
        return array_flip(self::COMPARISON_LABELS);
    }

    /**
     * @return array
     */
    public static function eqNotEq()
    {
        $array = [
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function fullEqNotEq()
    {
        $array = [
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_IS_NULL,
            ConditionInterface::TOKEN_IS_NOT_NULL,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function fullText()
    {
        $array = [
            ConditionInterface::TOKEN_CONTAINS,
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_BEGINS_WITH,
            ConditionInterface::TOKEN_ENDS_WITH,
            ConditionInterface::TOKEN_NOT_CONTAINS,
            ConditionInterface::TOKEN_NOT_BEGINS_WITH,
            ConditionInterface::TOKEN_NOT_ENDS_WITH,
            ConditionInterface::TOKEN_IS_NULL,
            ConditionInterface::TOKEN_IS_NOT_NULL,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function fullInt()
    {
        $array = [
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_GREATER_THAN,
            ConditionInterface::TOKEN_GREATER_THAN_OR_EQUAL,
            ConditionInterface::TOKEN_LESS_THAN,
            ConditionInterface::TOKEN_LESS_THAN_OR_EQUAL,
            ConditionInterface::TOKEN_IS_NULL,
            ConditionInterface::TOKEN_IS_NOT_NULL,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function mediumText()
    {
        $array = [
            ConditionInterface::TOKEN_CONTAINS,
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_NOT_CONTAINS,
            ConditionInterface::TOKEN_IS_NULL,
            ConditionInterface::TOKEN_IS_NOT_NULL,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function mediumInt()
    {
        $array = [
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_LESS_THAN,
            ConditionInterface::TOKEN_GREATER_THAN,
            ConditionInterface::TOKEN_IS_NULL,
            ConditionInterface::TOKEN_IS_NOT_NULL,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function smallText()
    {
        $array = [
            ConditionInterface::TOKEN_CONTAINS,
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_NOT_CONTAINS,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function smallInt()
    {
        $array = [
            ConditionInterface::TOKEN_EQUALS,
            ConditionInterface::TOKEN_NOT_EQUALS,
            ConditionInterface::TOKEN_GREATER_THAN,
            ConditionInterface::TOKEN_LESS_THAN,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }

    /**
     * @return array
     */
    public static function between()
    {
        $array = [
            ConditionInterface::TOKEN_BETWEEN,
            ConditionInterface::TOKEN_NOT_BETWEEN,
        ];

        return array_intersect(array_flip(self::COMPARISON_LABELS), $array);
    }
}
