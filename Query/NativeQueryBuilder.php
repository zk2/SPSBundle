<?php
namespace Zk2\SPSBundle\Query;

use Symfony\Component\Form\Form;

/**
 * Build a query from a given form object,
 * we basically add conditions to the Native query.
 */
class NativeQueryBuilder extends QueryBuilder
{
    /**
     * Build a filter Native query.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Doctrine\ORM\NativeQuery $query
     * @return \Doctrine\ORM\NativeQuery
     */
    public function buildQuery(Form $form, $query)
    {
        $this->query = $query;

        $group_child = $this->groupChild($form);

        $sql = $query->getSQL();

        foreach ($group_child as $field => $child) {
            if ($condition = $this->applyFilter($child, $field)) {
                $sql .= ' AND '.$condition;
            }
        }

        return $query->setSQL($sql)->setParameters($this->parameters);
    }
}