<?php

namespace Zk2\SPSBundle\Model;

use Zk2\SPSBundle\Model\SPS;


/**
 * Class DoctrineSPS
 */
class DoctrineSPS extends SPS
{
    /**
     * buildQuery
     *
     * Building a query without conditions
     *
     * @param string $rootModel
     * @param string $rootEntityAlias
     *
     * @return $this
     */
    public function buildQuery($rootModel, $rootEntityAlias)
    {
        $this->query = $this->getEm()->getRepository($rootModel)->createQueryBuilder($rootEntityAlias);

        return $this;
    }

    /**
     * Get Paginator
     *
     * @return \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination
     */
    protected function getPaginator()
    {
        $pn = $this->paginator->paginate(array());
        $sort_name = $pn->getPaginatorOption('sortFieldParameterName');
        $sort_direction_name = $pn->getPaginatorOption('sortDirectionParameterName');
        $page_name = $pn->getPaginatorOption('pageParameterName');
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
        if($sort = $this->session->get('_sps_sort_'.$this->ukey)){
            $this->paginator_options = array_merge(
                $this->paginator_options,
                $sort
            );
        } elseif (!$sort and isset($this->paginator_options['default_sort']) and $this->paginator_options['default_sort']
        ) {
            foreach ($this->paginator_options['default_sort'] as $field => $type) {
                $this->query->addOrderBy($field, $type);
            }
        }

        $pagination = $this->paginator->paginate(
            $this->query,
            $page,
            $this->paginator_limit,
            $this->paginator_options
        );

        $pagination->setTemplate($this->options['pagination_template']);
        $pagination->setSortableTemplate($this->options['sortable_template']);

        return compact('pagination');
    }

    protected function getAutosum()
    {
        $select = '';
        foreach($this->columns as $coll){
            if($alias = $coll->getAttr('autosum')){
                $select .= sprintf("SUM(%s) AS %s,", $coll->getAliasDotName(), $alias);
            }
        }
        if($select = trim($select, ',')){
            $q = strstr($this->query->getQuery()->getDql(), " FROM ");
            $q = sprintf("SELECT %s %s", $select, $q);

            $sumQuery = $this->getEm()->createQuery($q)
                ->setParameters($this->query->getQuery()->getParameters())
                ->setMaxResults(1);

            try {
                return $sumQuery->getSingleResult();
            } catch (\Doctrine\Orm\NoResultException $e) {

            }
        }
        return null;
    }
}