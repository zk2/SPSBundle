<?php

namespace Zk2\SPSBundle\Model;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Zk2\SPSBundle\Form\Type\SPSFilterType;

/**
 * Class SPS
 */
abstract class SPS
{
    /**
     * @var
     */
    protected $rootModel;

    /**
     * @var
     */
    protected $rootAlias;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $em;

    /**
     * @var
     */
    protected $query;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $form_factory;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var
     */
    protected $filter_form;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var FormFilterSession
     */
    protected $form_filter_session;

    /**
     * @var \Knp\Component\Pager\Paginator
     */
    protected $paginator;

    /**
     * @var int
     */
    protected $paginator_limit = 30;

    /**
     * @var array
     */
    protected $paginator_options = array();

    /**
     * @var mixed
     */
    protected $pagination_template;

    /**
     * @var mixed
     */
    protected $sortable_template;

    /**
     * @var array
     */
    protected $column_types = array(
        'string',
        'numeric',
        'boolean',
        'image',
        'datetime',
        'button',
    );

    /**
     * @var array
     */
    protected $filter_types = array(
        'text',
        'numeric',
        'choice',
        'boolean',
        'date',
    );

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
        $this->request = $request->getCurrentRequest();
        $this->container = $container;
        $this->session = $session;
        $this->queryBuilder = $queryBuilder;
        $this->em_name = $em_name;
        $this->em = $this->container->get('doctrine')->getManager($em_name);
        $this->form_factory = $this->container->get('form.factory');
        $this->form_filter_session = $this->container->get('zk2.sps.form_filter.session');
        $this->paginator = $this->container->get('knp_paginator');
        $this->pagination_template = $this->container->getParameter('zk2_sps.pagination_template');
        $this->sortable_template = $this->container->getParameter('zk2_sps.sortable_template');
    }

    /**
     * Is Reset
     *
     * Check whether the event to reset all filters
     * If true - remove sessipn variable this filter
     *
     * @return boolean
     */
    public function isReset()
    {
        if ($this->request->query->get('_reset')) {
            $this->session->remove('_filter_'.$this->request->get('_route'));
            $this->session->remove('_pager_'.$this->request->get('_route'));

            return $this->request->get('_route');
        }

        return null;
    }

    /**
     * @param $rootModel
     * @param $rootAlias
     * @return $this
     */
    public function setRoot($rootModel, $rootAlias)
    {
        $this->rootModel = $rootModel;
        $this->rootAlias = $rootAlias;

        return $this;
    }

    /**
     * @return mixed
     */
    abstract protected function getPaginator();

    /**
     * Get Entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * Get Query
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Add Column
     *
     * @return $this
     */
    public function addColumn($alias, $field, $type = 'string', array $attr = array())
    {
        if (!in_array($type, $this->column_types)) {
            throw new \InvalidArgumentException(
                sprintf("Column's type \"%s\" is not valid. Use %s", $type, implode(' or ', $this->column_types))
            );
        }
        $this->columns[$alias.'.'.$field] = new ColumnField($alias, $field, $type, $attr);

        return $this;
    }

    /**
     * Add Filter
     *
     * @return $this
     */
    public function addFilter($alias, $field, $type = 'text', array $attr = array())
    {
        if (!in_array($type, $this->filter_types)) {
            throw new \InvalidArgumentException(
                sprintf("Filter's type \"%s\" is not valid. Use %s", $type, implode(' or ', $this->filter_types))
            );
        }

        if (isset($attr['choices']) and $attr['choices']) {
            $attr['choices'] = $this->reconfigureChoices($attr['choices']);
        }

        if (!isset($attr['label']) and isset($this->columns[$alias.'.'.$field])) {
            $attr['label'] = $this->columns[$alias.'.'.$field]->getLabel();
        }

        $this->filters[$alias.'.'.$field] = new FilterField($alias, $field, $type, $attr);

        return $this;
    }

    /**
     * reconfigureChoices
     *
     * @param array $choices
     *
     * @return array $choices
     */
    protected function reconfigureChoices($choices)
    {
        if (isset($choices[0]) and is_array($choices[0])) {
            $array = array();
            foreach ($choices as $data) {
                $array[array_shift($data)] = array_pop($data);
            }

            return $array;
        }

        return $choices;
    }

    /**
     * checkFilters
     *
     * Build filters form
     * If the method POST filters are constructed and written to the session
     * else if in session is a form of filter-it is Otherwise,
     * if there is a default filters-they are
     *
     * @param array $default (stand default filter (option))
     * @return
     */
    protected function checkFilters($default = array())
    {
        if ($this->filters) {
            $this->filter_form = $this->form_factory->create(new SPSFilterType($this->filters));

            if (count($default)) {
                $this->filter_form->setData($default);
            }

            $this->filter_form->handleRequest($this->request);

            if ($this->filter_form->getErrors()->count()) {
                $this->session->getFlashBag()->add('error', 'Форма фильтра содержит ошибки...');
            }

            if ($this->filter_form->isValid()) {
                $this->session->remove('_filter_'.$this->request->get('_route'));
                $this->session->remove('_pager_'.$this->request->get('_route'));

                $this->queryBuilder->buildQuery($this->filter_form, $this->query);

                $this->form_filter_session->serialize(
                    $this->filter_form->getData(),
                    '_filter_'.$this->request->get('_route')
                );
            } elseif ($this->session->has('_filter_'.$this->request->get('_route'))) {
                $data = $this->form_filter_session->unserialize(
                    '_filter_'.$this->request->get('_route'),
                    $this->em_name
                );

                $this->filter_form->setData($data);
                $this->queryBuilder->buildQuery($this->filter_form, $this->query);
            } elseif (count($default)) {
                $this->buildQuery->buildQuery($this->filter_form, $this->query);
            }
        }
    }

    /**
     * setPaginatorLimit
     */
    public function setPaginatorLimit($num)
    {
        if (is_numeric($num)) {
            $this->paginator_limit = $num;
        }
    }

    /**
     * setPaginator
     */
    public function setPaginatorOptions(array $options)
    {
        $this->paginator_options = $options;
    }

    /**
     * Build Result
     *
     * @return array
     */
    public function buildResult()
    {
        $this->checkFilters();

        return array(
            'columns' => $this->columns,
            'filter_form' => $this->filter_form->createView(),
            'paginator' => $this->getPaginator(),
        );
    }
}
