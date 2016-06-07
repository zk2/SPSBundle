<?php

namespace Zk2\SPSBundle\Model;


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
        $paginatorTmp = $this->paginator->paginate(array());
        $sortName = $paginatorTmp->getPaginatorOption('sortFieldParameterName');
        $sortDirectionName = $paginatorTmp->getPaginatorOption('sortDirectionParameterName');
        $pageName = $paginatorTmp->getPaginatorOption('pageParameterName');
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

        if ($sort = $this->session->get('_sps_sort_' . $this->ukey)) {
            $this->query->addOrderBy($sort['defaultSortFieldName'], $sort['defaultSortDirection']);
        } elseif (!$sort and isset($this->paginatorOptions['default_sort']) and $this->paginatorOptions['default_sort']) {
            foreach ($this->paginatorOptions['default_sort'] as $field => $type) {
                $this->query->addOrderBy($field, $type);
            }
        }

        $pagination = $this->paginator->paginate(
            $this->query->getQuery()->getResult(),
            $page,
            $this->paginatorLimit,
            $this->paginatorOptions
        );

        $pagination->setTemplate($this->options['pagination_template']);
        $pagination->setSortableTemplate($this->options['sortable_template']);

        return compact('pagination');
    }

    protected function getAutosum()
    {
        $select = '';
        /** @var ColumnField $coll */
        foreach ($this->columns as $coll) {
            if ($alias = $coll->getAttr('autosum')) {
                $select .= sprintf("SUM(%s) AS %s,", $coll->getAliasDotName(), $alias);
            }
        }
        if ($select = trim($select, ',')) {
            $this->query->resetDQLParts(array(
                "groupBy",
                "having",
                "orderBy"
            ));
            $this->query->add('select', $select);

            $sumQuery = $this->getEm()->createQuery($this->query->getQuery()->getDql())
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