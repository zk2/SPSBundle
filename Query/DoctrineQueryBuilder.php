<?php
namespace Zk2\SPSBundle\Query;

use Symfony\Component\Form\Form;

/**
 * Build a query from a given form object,
 * we basically add conditions to the Doctrine query builder.
 */
class DoctrineQueryBuilder extends QueryBuilder
{
    /**
     * Build a filter query.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Doctrine\ORM\QueryBuilder $query
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildQuery(Form $form, $query)
    {
        $this->query = $query;

        $group_child = $this->groupChild($form);

        foreach ($group_child as $children) {
            if ($condition = $this->applyFilter($children)) {
                $query->andWhere($condition);
            }
        }

        return $query->setParameters($this->parameters);
    }
}