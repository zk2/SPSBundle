<?php

namespace Zk2\SPSBundle\Model;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class DoctrineSPS
 */
class DoctrineSPS extends SPS
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
    public function buildQuery()
    {
        $this->query = $this->em->getRepository($this->rootModel)->createQueryBuilder($this->rootAlias);

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

        if ($this->request->query->has('page')) {
            $this->session->set('_pager_'.$this->request->get('_route'), $page);
        } elseif ($this->session->has('_pager_'.$this->request->get('_route'))) {
            $page = $this->session->get('_pager_'.$this->request->get('_route'));
        }

        if ((!$this->request->query->has('sort') or !$this->request->query->has('direction'))
            and isset($this->paginator_options['default_sort']) and $this->paginator_options['default_sort']
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

        $pagination->setTemplate($this->pagination_template);
        $pagination->setSortableTemplate($this->sortable_template);

        return compact('pagination');
    }
}