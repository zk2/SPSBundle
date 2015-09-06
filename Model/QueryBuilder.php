<?php
namespace Zk2\SPSBundle\Model;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NativeQuery;
use Symfony\Component\Form\Form;

/**
 * Build a query from a given form object,
 */
abstract class QueryBuilder
{
    protected $query;

    protected $platform_name;

    protected $parameters = array();

    protected function getPlatfornName()
    {
        if ($this->query instanceof AbstractQuery) {
            return $this->platform_name = $this->query->getEntityManager()->getConnection()->getDatabasePlatform(
            )->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function groupChild(Form $form)
    {
        $group_child = array();

        $this->platform_name = $this->getPlatfornName();

        foreach ($form->all() as $child) {
            $group_child[$child->getConfig()->getOption('sps_global_name')][] = $child;
        }

        return $group_child;
    }

    /**
     * Build a filter query.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Doctrine\ORM\QueryBuilder $query
     * @return \Doctrine\ORM\QueryBuilder
     */
    abstract public function buildQuery(Form $form, $query);

    /**
     * applyFilter
     */
    protected function applyFilter($children)
    {
        $condition = '';

        foreach ($children as $i => $child) {
            if ($child->getConfig()->getOption('not_used')) {
                continue;
            }

            if ($child->get('name')->getData() instanceof \DateTime) {
                $get_name = $child->get('name')->getData()->format('Y-m-d H:i:s');
            } else {
                $get_name = $child->get('name')->getData();
            }

            $alias = $child->getConfig()->getOption('sps_global_alias');
            $field = $child->getConfig()->getOption('sps_global_name');
            $form_field_type = $child->getConfig()->getOption('sps_type');

            $paramName = sprintf('%s_%s_param_%s', $alias, $field, $i);

            $or_and = ($child->has('condition_pattern')) ? $child->get('condition_pattern')->getData() : ' ';
            $condition_operator = $child->get('condition_operator')->getData();

            if (in_array($condition_operator, array('IS NULL', 'IS NOT NULL'))) {
                $condition .= sprintf('%s (%s.%s %s)', $or_and, $alias, $field, $condition_operator);
            } elseif (trim((string)$get_name) != '') {
                $value = sprintf(str_replace('x', '', $condition_operator), (string)$get_name);

                switch ($condition_operator) {
                    case '%s'     :
                        $operator = '=';
                        break;
                    case 'x%s'    :
                        $operator = '<>';
                        break;
                    case 'xx%s'   :
                        $operator = '>';
                        break;
                    case 'xxx%s'  :
                        $operator = '<';
                        break;
                    case 'xxxx%s' :
                        $operator = '>=';
                        break;
                    case 'xxxxx%s':
                        $operator = '<=';
                        break;
                    case '%%%s%%' :
                        $operator = 'LIKE';
                        break;
                    case '%s%%'   :
                        $operator = 'LIKE';
                        break;
                    case '%%%s'   :
                        $operator = 'LIKE';
                        break;
                    case 'x%%%s%%':
                        $operator = 'NOT LIKE';
                        break;
                    case 'x%s%%'  :
                        $operator = 'NOT LIKE';
                        break;
                    case 'x%%%s'  :
                        $operator = 'NOT LIKE';
                        break;
                }

                $alias_dot_field = sprintf("%s.%s", $alias, $field);

                if ($form_field_type == 'date' and $child->getConfig()->getOption(
                        'use_timezone'
                    ) and $mz = $child->getConfig()->getOption('model_timezone') and $vz = $child->getConfig(
                    )->getOption(
                        'view_timezone'
                    )
                ) {
                    if ($this->query instanceof NativeQuery) {
                        if ('postgresql' == $this->platform_name) {
                            $alias_dot_field = sprintf(
                                "date(\"timestamp\"(%s) AT TIME ZONE '%s' AT TIME ZONE '%s')",
                                $alias_dot_field,
                                $vz,
                                $mz
                            );
                        } elseif ('mysql' == $this->platform_name) {
                            sprintf("DATE(CONVERT_TZ(%s,'%s','%s'))", $alias_dot_field, $vz, $mz);
                        }
                    } else {
                        $alias_dot_field = sprintf("date(convert_tz(%s,'%s','%s'))", $alias_dot_field, $mz, $vz);
                    }
                }

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
        $condition = str_replace('noalias.', '', $condition);
        $condition = trim($condition);
        $condition = trim($condition, 'AND');
        $condition = trim($condition, 'OR');

        return $condition;
    }
}