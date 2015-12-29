<?php

namespace Zk2\SPSBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Zk2\SPSBundle\Form\Type\SPSType;
use Zk2\SPSBundle\Query\QueryBuilder;
use Zk2\SPSBundle\Utils\FormFilterSession;

/**
 * Class SPS
 */
abstract class SPS
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $em_name = 'default';

    /**
     * @var \Zk2\SPSBundle\Query\QueryBuilder
     */
    protected $queryBuilder;

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
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var
     */
    protected $query;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var mixed
     */
    protected $autosum;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $defaultFilters = array();

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $filterForm;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Zk2\SPSBundle\Utils\FormFilterSession
     */
    protected $formFilterSession;

    /**
     * @var array
     */
    protected $options = array();
    
    /**
     * @var array
     */
    protected $replaceRules = array('from' => null, 'to' => null);

    /**
     * @var array
     */
    protected $column_types = array(
        'text',
        'string',
        'numeric',
        'boolean',
        'datetime',
        'image',
        'button',
    );

    /**
     * @var array
     */
    protected $filter_types = array(
        'text',
        'string',
        'numeric',
        'boolean',
        'date',
        'choice',
    );

    /**
     * @var string
     */
    protected $totalRoute;

    /**
     * @var array
     */
    protected $totalRouteParams = array();

    /**
     * @var string
     */
    protected $ukey;

    /**
     * @param RequestStack $request
     * @param Registry $doctrine
     * @param Paginator $paginator
     * @param FormFactory $formFactory
     * @param Router $router
     * @param FormFilterSession $formFilterSession
     * @param array $options
     */
    public function __construct(
        RequestStack $request,
        Registry $doctrine,
        Paginator $paginator,
        FormFactory $formFactory,
        Router $router,
        FormFilterSession $formFilterSession,
        array $options
    ) {
        $this->request = $request->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->formFilterSession = $formFilterSession;
        $this->options = $options;
        $this->totalRoute = $this->request->get('_route');
        $this->totalRouteParams = $this->request->get('_route_params');
        $this->ukey = $this->totalRoute.(http_build_query($this->totalRouteParams, null, '_sps_'));
    }

    /**
     * @return mixed
     */
    abstract protected function getPaginator();

    /**
     * buildQuery
     *
     * @param string $param1 - if doctrineSPS ? rootModel : SQL query
     * @param string $param1 - if doctrineSPS ? rootEntityAlias : array fields for \Doctrine\ORM\Query\ResultSetMapping
     *
     * @return $this
     */
    abstract protected function buildQuery($param1, $param2);

    /**
     * @param $em_name
     *
     * @return $this
     */
    public function setEmName($em_name)
    {
        $this->em_name = $em_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmName()
    {
        return $this->em_name;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Is Reset
     *
     * Check whether the event to reset all filters
     * If true - remove sessipn variable this filter
     *
     * @return string|null
     */
    public function isReset()
    {
        if ($this->request->query->get('_sps_reset')) {
            $this->session->remove('_sps_filter_'.$this->ukey);
            $this->session->remove('_sps_pager_'.$this->ukey);
            $this->session->remove('_sps_sort_'.$this->ukey);

            return $this->router->generate($this->totalRoute, $this->totalRouteParams);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUkey()
    {
        return $this->ukey;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEm()
    {
        return $this->doctrine->getManager($this->em_name);
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $alias
     * @param $field
     * @param string $type
     * @param array $attr
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
     * @param array $defaultFilters
     */
    public function setDefaultFilters(array $defaultFilters)
    {
        $this->defaultFilters = $defaultFilters;
    }

    /**
     * @param $alias
     * @param $field
     * @param string $type
     * @param array $attr
     * @return $this
     */
    public function addFilter($alias, $field, $type = 'string', array $attr = array())
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
     * @return null
     */
    protected function checkFilters()
    {
        if ($this->filters) {
            $this->filterForm = $this->formFactory->create(new SPSType($this->filters), null, array(
                'action' => $this->router->generate($this->totalRoute, $this->totalRouteParams)
            ));
            $this->filterForm->setData($this->defaultFilters);
            $this->filterForm->handleRequest($this->request);

            if ($this->filterForm->getErrors()->count()) {
                $this->session->getFlashBag()->add('error', 'The form of the filter contains errors...');
            }

            if ($this->filterForm->isValid()) {
                $this->session->remove('_sps_filter_'.$this->ukey);
                $this->session->remove('_sps_pager_'.$this->ukey);

                $this->queryBuilder->buildQuery($this->filterForm, $this->query);

                $this->formFilterSession->serialize(
                    $this->filterForm->getData(),
                    '_sps_filter_'.$this->ukey
                );
            } elseif ($this->session->has('_sps_filter_'.$this->ukey)) {
                $data = $this->formFilterSession->unserialize(
                    '_sps_filter_'.$this->ukey,
                    $this->em_name
                );

                $this->filterForm->setData($data);
                $this->queryBuilder->buildQuery($this->filterForm, $this->query);
            } elseif (count($this->defaultFilters)) {
                $this->queryBuilder->buildQuery($this->filterForm, $this->query);
            }
        }
    }

    /**
     * @param $num
     */
    public function setPaginatorLimit($num)
    {
        if (is_numeric($num)) {
            $this->paginator_limit = $num;
        }
    }

    /**
     * @param array $options
     */
    public function setPaginatorOptions(array $options)
    {
        $this->paginator_options = $options;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOptions($name,$value)
    {
        if(!isset($this->options[$name])){
            throw new \InvalidArgumentException(
                sprintf("Option %s is not valid", $name)
            );
        }
        $this->options[$name] = $value;
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
            'filter_form' => $this->filterForm->createView(),
            'paginator' => $this->getPaginator(),
            'autosum' => $this->getAutosum(),
        );
    }

    /**
     * get Autosum
     *
     * @return mixed
     */
    protected function getAutosum()
    {
        return $this->autosum;
    }

    /**
     * replace query
     *
     * @return string
     */
    protected function replaceQuery()
    {
        if($this->query instanceof \Doctrine\ORM\NativeQuery){
            $query = $this->query->getSQL();
        } else {
            $query = $this->query->getQuery()->getSQL();
        } 
        
        if(null === $this->replaceRules['from'] or null === $this->replaceRules['to']){
            return $query;
        }
        
        return str_replase($this->replaceRules['from'], $this->replaceRules['to'], $query);
    }

    /**
     * Set replace rules
     *
     * @param $from
     * @param $to
     * @return $this
     */
    public function setReplaceRules($from, $to)
    {
        $this->replaceRules['from'] = $from;
        $this->replaceRules['to'] = $to;
        
        return $this;
    }
}
