<?php

namespace Zk2\SPSBundle\Query;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NativeQuery;
use Symfony\Component\Form\Form;
use Zk2\SPSBundle\Utils\ConditionOperator;

/**
 * Class QueryBuilder
 * @package Zk2\SPSBundle\Query
 */
abstract class QueryBuilder
{
    /**
     * @var
     */
    protected $query;

    /**
     * @var
     */
    protected $platform_name;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Build a filter query.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Doctrine\ORM\QueryBuilder $query
     */
    abstract public function buildQuery(Form $form, $query);

    /**
     * @return string
     */
    protected function getPlatformName()
    {
        if ($this->query instanceof AbstractQuery) {
            $this->platform_name = $this->query->getEntityManager()->getConnection()->getDatabasePlatform()->getName();
        }

        return $this->platform_name;
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function groupChild(Form $form)
    {
        $group_child = array();

        $this->platform_name = $this->getPlatformName();

        foreach ($form->all() as $child) {
            $key = $child->getConfig()->getOption('sps_field_alias')
                .'.'.$child->getConfig()->getOption('sps_field_name');
            $group_child[$key][] = $child;
        }

        return $group_child;
    }

    /**
     * @param $children
     * @return string
     */
    protected function applyFilter($children)
    {
        $condition = '';

        foreach ($children as $i => $child) {
            if ($child->getConfig()->getOption('not_used')) {
                continue;
            }

            $alias = $child->getConfig()->getOption('sps_field_alias');
            $field = $child->getConfig()->getOption('sps_field_name');
            $form_field_type = $child->getConfig()->getOption('sps_field_type');

            $paramName = sprintf('%s_%s_param_%s', $alias, $field, $i);

            $or_and = ($child->has('condition_pattern')) ? $child->get('condition_pattern')->getData() : ' ';
            $condition_operator = $child->get('condition_operator')->getData();

            $get_value = $child->get('name')->getData();
            if (!($get_value instanceof \DateTime)) {
                $get_value = trim((string)$get_value);
            }

            if (in_array($condition_operator, array('IS NULL', 'IS NOT NULL'))) {
                $condition .= sprintf('%s (%s.%s %s)', $or_and, $alias, $field, $condition_operator);
            } elseif ($get_value) {
                $operator = ConditionOperator::getOperator($condition_operator);
                $alias_dot_field = 'noalias' == $alias ? $field : sprintf("%s.%s", $alias, $field);
                if($get_value instanceof \DateTime){
                    $condition .= sprintf(
                        '%s %s ',
                        $or_and,
                        $this->getD($get_value, $child->getConfig(), $operator, $alias_dot_field)
                    );
                } else {
                    $value = sprintf(str_replace('x', '', $condition_operator), $get_value);
                    $condition .= sprintf(
                        '%s %s %s :%s ',
                        $or_and,
                        $alias_dot_field,
                        $operator,
                        $paramName
                    );
                    $this->parameters[$paramName] = $value;
                }
            }
        }

        return $condition;
    }

    /**
     * @param \DateTime $value
     * @param $config
     * @param $operator
     * @param $alias_dot_field
     * @return string
     */
    protected function getD(\DateTime $value, $config, $operator, $alias_dot_field)
    {
        $view_timezone = $config->getOption('view_timezone');
        $model_timezone = $config->getOption('model_timezone');
        $from = clone $value;
        if($view_timezone != $model_timezone){
            $from->setTimezone(new \DateTimeZone($model_timezone));
        }
        $to = clone $from;
        $to->modify((60*60*24-1).' seconds');
        $f = $from->format('Y-m-d H:i:s');
        $t = $to->format('Y-m-d H:i:s');
        switch($operator){
            case '=':
                return sprintf("(%s >= '%s' AND %s <= '%s')", $alias_dot_field, $f, $alias_dot_field, $t);
            case '!=':
                return sprintf("(%s < '%s' AND %s > '%s')", $alias_dot_field, $f, $alias_dot_field, $t);
            case '>=':
                return sprintf("(%s >= '%s')", $alias_dot_field, $f);
            case '<=':
                return sprintf("(%s <= '%s')", $alias_dot_field, $t);
            case '>':
                return sprintf("(%s > '%s')", $alias_dot_field, $t);
            case '<':
                return sprintf("(%s < '%s')", $alias_dot_field, $f);
        }

        throw new \InvalidArgumentException('Operator "'.$operator.'" not valid...');
    }
}