<?php

namespace Zk2\SPSBundle\Model;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class NativeSPS
 */
class NativeSPS extends SPS
{
    /**
     * buildQuery
     *
     * Building a query without conditions
     *
     * @param string $query SQL query
     * @param array $fields for \Doctrine\ORM\Query\ResultSetMapping
     *
     * @return $this
     */
    public function buildQuery($query, $fields)
    {
        $rsm = new ResultSetMapping();
        foreach ($fields as $field) {
            $rsm->addScalarResult($field, $field);
        }
        $this->query = $this->getEm()->createNativeQuery($query, $rsm);

        return $this;
    }

    /**
     * Get Paginator
     *
     * @return \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination
     */
    protected function getPaginator()
    {
        $pagination = $this->paginator->paginate(
            array(),
            1,
            $this->paginatorLimit,
            $this->session->get('_sps_sort_' . $this->ukey, array())
        );

        $sortName = $pagination->getPaginatorOption('sortFieldParameterName');
        $sortDirectionName = $pagination->getPaginatorOption('sortDirectionParameterName');
        $pageName = $pagination->getPaginatorOption('pageParameterName');
        $page = $this->request->query->getInt($pageName, 1);

        if ($this->request->query->has($sortName)) {
            $this->session->set('_sps_sort_' . $this->ukey, array(
                'defaultSortFieldName' => $this->request->query->get($sortName),
                'defaultSortDirection' => $this->request->query->get($sortDirectionName, 'asc')
            ));
        }

        if ($this->request->query->has($pageName)) {
            $this->session->set('_sps_pager_' . $this->ukey, $page);
        } elseif ($this->session->has('_sps_pager_' . $this->ukey)) {
            $page = $this->session->get('_sps_pager_' . $this->ukey);
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('cnt', 'cnt');
        $queryForCnt = $this->replaceQuery();
        $q = sprintf("SELECT COUNT(*) cnt FROM (%s) zzz", $queryForCnt);
        $cntQuery = $this->getEm()->createNativeQuery($q, $rsm)->setParameters($this->query->getParameters());
        try {
            $cnt = $cntQuery->getSingleScalarResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $cnt = 0;
        }

        $sql = $this->query->getSQL();

        if ($sort = $this->session->get('_sps_sort_' . $this->ukey)) {
            $sql .= ' ORDER BY ' . $sort['defaultSortFieldName'] . ' ' . $sort['defaultSortDirection'];
        } elseif (isset($this->paginatorOptions['default_sort']) and $this->paginatorOptions['default_sort']) {
            $sql .= ' ORDER BY ';
            foreach ($this->paginatorOptions['default_sort'] as $field => $type) {
                $sql .= $field . ' ' . $type . ',';
            }
            $sql = trim($sql, ',');
        }

        $offset = $this->paginatorLimit * ($page - 1);
        $this->query->setSQL($sql . ' LIMIT ' . $this->paginatorLimit . ' OFFSET ' . $offset);

        $pagination->setCurrentPageNumber($page);
        $pagination->setItemNumberPerPage($this->paginatorLimit);
        $pagination->setTotalItemCount($cnt);
        $pagination->setItems($this->query->getResult());

        $pagination->setTemplate($this->options['pagination_template']);
        $pagination->setSortableTemplate($this->options['sortable_template']);

        return compact('pagination');
    }

    protected function getAutosum()
    {
        $select = '';
        $rsm = new ResultSetMapping();
        /** @var ColumnField $coll */
        foreach ($this->columns as $coll) {
            if ($alias = $coll->getAttr('autosum')) {
                $select .= sprintf("SUM(%s) AS %s,", $coll->getAliasDotName(), $alias);
                $rsm->addScalarResult($alias, $alias);
            }
        }
        if ($select = trim($select, ',')) {
            $q = $this->query->getSQL();
            $q = stristr($q, " FROM ");
            $q_without_group_by = stristr($q, "GROUP BY");
            $q = str_replace($q_without_group_by, " ", $q);
            $q_without_order_by = stristr($q, "ORDER BY");
            $q = str_replace($q_without_order_by, " ", $q);
            $q = sprintf("SELECT %s %s", $select, $q);
            $sumQuery = $this->getEm()->createNativeQuery($q, $rsm)->setParameters($this->query->getParameters());

            return $sumQuery->getOneOrNullResult();
        }

        return null;
    }
}
