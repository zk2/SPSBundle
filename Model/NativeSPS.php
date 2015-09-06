<?php

namespace Zk2\SPSBundle\Model;

use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class NativeSPS
 */
class NativeSPS extends SPS
{
    /**
     * @param RequestStack $request
     * @param Container $container
     * @param Session $session
     * @param QueryBuilder $queryBuilder
     * @param $em_name
     */
    public function __construct(
        RequestStack $request,
        Container $container,
        Session $session,
        QueryBuilder $queryBuilder,
        $em_name
    ) {
        parent::__construct(
            $request,
            $container,
            $session,
            $queryBuilder,
            $em_name
        );
    }

    /**
     * buildQuery
     *
     * Building a query without conditions
     *
     * @return $this
     */
    public function buildQuery($query, array $fields)
    {
        $rsm = new ResultSetMapping();
        foreach ($fields as $field) {
            $rsm->addScalarResult($field, $field);
        }
        $this->query = $this->em->createNativeQuery($query, $rsm);

        return $this;
    }

    /**
     * Get Paginator
     *
     * @param integer $limit ( limit per page )
     * @param array $options
     * @return \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination
     */
    protected function getPaginator()
    {
        $page = $this->request->query->get('page', 1);
        $cnt = 0;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('cnt', 'cnt');
        $q = strstr($this->query->getSQL(), " FROM ");
        $q = "SELECT COUNT(*) cnt ".$q;
        $cntQuery = $this->em->createNativeQuery($q, $rsm)
            ->setParameters($this->query->getParameters());
        try {
            $cnt = $cntQuery->getSingleScalarResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $cnt = 0;
        }

        $sql = $this->query->getSQL();

        $pagination = $this->paginator->paginate(array());

        $sort_name = $pagination->getPaginatorOption('sortFieldParameterName');
        $sort_direction_name = $pagination->getPaginatorOption('sortDirectionParameterName');

        if ($this->request->query->has($sort_name) and $this->request->query->has($sort_direction_name)) {
            $sql .= ' ORDER BY '.$this->request->query->get($sort_name).' '.$this->request->query->get(
                    $sort_direction_name
                );
        } elseif (isset($this->paginator_options['default_sort']) and $this->paginator_options['default_sort']) {
            $sql .= ' ORDER BY ';
            foreach ($this->paginator_options['default_sort'] as $field => $type) {
                $sql .= $field.' '.$type.',';
            }
            $sql = trim($sql, ',');
        }

        if (!isset($this->paginator_options['not_use_limit_offset'])) {
            $offset = $this->paginator_limit * ($page - 1);
            $this->query->setSQL($sql.' LIMIT '.$this->paginator_limit.' OFFSET '.$offset);
        }

        $pagination->setCurrentPageNumber($page);
        $pagination->setItemNumberPerPage($this->paginator_limit);
        $pagination->setTotalItemCount($cnt);
        $pagination->setItems($this->query->getResult());

        $pagination->setTemplate($this->pagination_template);
        $pagination->setSortableTemplate($this->sortable_template);

        return compact('pagination');
    }
}