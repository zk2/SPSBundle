<?php

namespace Zk2\SPSBundle\Query;

use Doctrine\ORM\AbstractQuery;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Zk2\SPSBundle\Exceptions\InvalidArgumentException;
use Zk2\SPSBundle\Model\ColumnField;
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
    protected $platformName;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Build a filter query.
     *
     * @param Form $form
     * @param $query
     */
    abstract public function buildQuery(Form $form, $query);

    /**
     * @return string
     */
    protected function getPlatformName()
    {
        if ($this->query instanceof AbstractQuery) {
            $this->platformName = $this->query->getEntityManager()->getConnection()->getDatabasePlatform()->getName();
        }

        return $this->platformName;
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function groupChild(Form $form)
    {
        $groupChild = array();

        //$this->platformName = $this->getPlatformName();

        /** @var Form $child */
        foreach ($form->all() as $child) {
            $key = $child->getConfig()->getOption('sps_field_alias') . '.' . $child->getConfig()->getOption('sps_field_name');
            $groupChild[$key][] = $child;
        }

        return $groupChild;
    }

    /**
     * @param array $children
     * @return string
     */
    protected function applyFilter(array $children)
    {
        $condition = '';
        /** @var Form $child */
        foreach ($children as $i => $child) {
            if ($child->getConfig()->getOption('not_used')) {
                continue;
            }
            $alias = $child->getConfig()->getOption('sps_field_alias');
            $field = $child->getConfig()->getOption('sps_field_name');
            $formFieldType = $child->getConfig()->getOption('sps_field_type');
            $paramName = sprintf('%s_%s_param_%s', $alias, $field, $i);
            $andOr = ($child->has('condition_pattern')) ? $child->get('condition_pattern')->getData() : ' ';
            $conditionOperator = $child->get('condition_operator')->getData();
            $aliasDotField = ColumnField::NOALIAS == $alias ? $field : sprintf("%s.%s", $alias, $field);

            if (in_array($conditionOperator, array('IS NULL', 'IS NOT NULL'))) {
                $condition .= sprintf('%s (%s %s)', $andOr, $aliasDotField, $conditionOperator);
            } else {
                $operator = ConditionOperator::getOperator($conditionOperator);
                if (in_array($formFieldType, array('dateRange', 'date'))) {
                    if ('dateRange' == $formFieldType) {
                        $start = $child->get('name')->get('start')->getData();
                        $end = $child->get('name')->get('end')->getData();
                        if ($end and !$start) $start = clone $end;
                    } else {
                        $start = $child->get('name')->getData();
                        $end = null;
                    }
                    if ($start instanceof \DateTime) {
                        $condition .= sprintf(
                            '%s %s ',
                            $andOr,
                            $this->getBuildDateCondition($child->getConfig(), $operator, $aliasDotField, $start, $end)
                        );
                    }
                } elseif($fieldValue = trim((string) $child->get('name')->getData())) {
                    $value = sprintf(str_replace('x', '', $conditionOperator), $fieldValue);
                    $condition .= sprintf(
                        '%s %s %s :%s ',
                        $andOr,
                        $aliasDotField,
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
     * @param FormConfigInterface $config
     * @param $operator
     * @param $aliasDotField
     * @param \DateTime $start
     * @param \DateTime|null $end
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getBuildDateCondition(
        FormConfigInterface $config,
        $operator,
        $aliasDotField,
        \DateTime $start,
        \DateTime $end = null
    )
    {
        $dateFrom = clone $start;
        if ($config->getOption('view_timezone') != $config->getOption('model_timezone')) {
            $dateFrom->setTimezone(new \DateTimeZone($config->getOption('model_timezone')));
        }
        $dateFrom->setTime(0,0,0);
        $dateTo = $end ? clone $end : clone $dateFrom;
        $dateTo->setTime(23,59,59);
        $from = $dateFrom->format('Y-m-d H:i:s');
        $to = $dateTo->format('Y-m-d H:i:s');
        switch ($operator) {
            case '=':
                return sprintf("(%s >= '%s' AND %s <= '%s')", $aliasDotField, $from, $aliasDotField, $to);
            case '!=':
                return sprintf("(%s < '%s' OR %s > '%s')", $aliasDotField, $from, $aliasDotField, $to);
            case '>=':
                return sprintf("(%s >= '%s')", $aliasDotField, $from);
            case '<=':
                return sprintf("(%s <= '%s')", $aliasDotField, $to);
            case '>':
                return sprintf("(%s > '%s')", $aliasDotField, $to);
            case '<':
                return sprintf("(%s < '%s')", $aliasDotField, $from);
        }

        throw new InvalidArgumentException('Operator "' . $operator . '" is not valid...');
    }

    /**
     * @param \DateTime $value
     * @param FormConfigInterface $config
     * @param $operator
     * @param $aliasDotField
     * @return string
     * @throws InvalidArgumentException
     */
    /*protected function getBuildDateCondition(\DateTime $value, FormConfigInterface $config, $operator, $aliasDotField)
    {
        $dateFrom = clone $value;
        if ($config->getOption('view_timezone') != $config->getOption('model_timezone')) {
            $dateFrom->setTimezone(new \DateTimeZone($config->getOption('model_timezone')));
        }
        $dateTo = clone $dateFrom;
        $dateTo->modify((60 * 60 * 24 - 1) . ' seconds');
        $from = $dateFrom->format('Y-m-d H:i:s');
        $to = $dateTo->format('Y-m-d H:i:s');
        switch ($operator) {
            case '=':
                return sprintf("(%s >= '%s' AND %s <= '%s')", $aliasDotField, $from, $aliasDotField, $to);
            case '!=':
                return sprintf("(%s < '%s' AND %s > '%s')", $aliasDotField, $from, $aliasDotField, $to);
            case '>=':
                return sprintf("(%s >= '%s')", $aliasDotField, $from);
            case '<=':
                return sprintf("(%s <= '%s')", $aliasDotField, $to);
            case '>':
                return sprintf("(%s > '%s')", $aliasDotField, $to);
            case '<':
                return sprintf("(%s < '%s')", $aliasDotField, $from);
        }

        throw new InvalidArgumentException('Operator "' . $operator . '" is not valid...');
    }*/
}