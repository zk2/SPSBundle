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
            $this->paginator_limit,
            $this->session->get('_sps_sort_'.$this->ukey, array())
        );

        $sort_name = $pagination->getPaginatorOption('sortFieldParameterName');
        $sort_direction_name = $pagination->getPaginatorOption('sortDirectionParameterName');
        $page_name = $pagination->getPaginatorOption('pageParameterName');
        $page = $this->request->query->getInt($page_name, 1);

        if ($this->request->query->has($sort_name)) {
            $this->session->set('_sps_sort_'.$this->ukey, array(
                'defaultSortFieldName' => $this->request->query->get($sort_name),
                'defaultSortDirection' => $this->request->query->get($sort_direction_name, 'asc')
            ));
        }

        if ($this->request->query->has($page_name)) {
            $this->session->set('_sps_pager_'.$this->ukey, $page);
        } elseif ($this->session->has('_sps_pager_'.$this->ukey)) {
            $page = $this->session->get('_sps_pager_'.$this->ukey);
        }

        $cnt = 0;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('cnt', 'cnt');
        $q = sprintf("SELECT COUNT(*) cnt FROM(%s) zzz", $this->query->getSQL());
        $cntQuery = $this->getEm()->createNativeQuery($q, $rsm)->setParameters($this->query->getParameters());
        try {
            $cnt = $cntQuery->getSingleScalarResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $cnt = 0;
        }

        $sql = $this->query->getSQL();

        if ($sort = $this->session->get('_sps_sort_'.$this->ukey)) {
            $sql .= ' ORDER BY '.$sort['defaultSortFieldName'].' '.$sort['defaultSortDirection'];
        } elseif (isset($this->paginator_options['default_sort']) and $this->paginator_options['default_sort']) {
            $sql .= ' ORDER BY ';
            foreach ($this->paginator_options['default_sort'] as $field => $type) {
                $sql .= $field.' '.$type.',';
            }
            $sql = trim($sql, ',');
        }

        $offset = $this->paginator_limit * ($page - 1);
        $this->query->setSQL($sql.' LIMIT '.$this->paginator_limit.' OFFSET '.$offset);

        $pagination->setCurrentPageNumber($page);
        $pagination->setItemNumberPerPage($this->paginator_limit);
        $pagination->setTotalItemCount($cnt);
        $pagination->setItems($this->query->getResult());

        $pagination->setTemplate($this->options['pagination_template']);
        $pagination->setSortableTemplate($this->options['sortable_template']);

        return compact('pagination');
    }
}