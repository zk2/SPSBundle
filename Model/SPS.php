<?php

namespace Zk2\SPSBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Zk2\SPSBundle\Exceptions\InvalidArgumentException;
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
    protected $emName = 'default';

    /**
     * @var \Zk2\SPSBundle\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var int
     */
    protected $paginatorLimit = 30;

    /**
     * @var array
     */
    protected $paginatorOptions = array();

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var AbstractQuery|\Doctrine\ORM\QueryBuilder
     */
    protected $query;

    /**
     * @var array
     */
    protected $columns = array();

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
     * @var \Zk2\SPSBundle\Utils\FormFilterSession
     */
    protected $formFilterSession;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

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
    protected $columnTypes = array(
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
    protected $filterTypes = array(
        'string',
        'numeric',
        'boolean',
        'date',
        'dateRange',
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
     * @var mixed
     */
    protected $autosum;

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
    )
    {
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
        $this->ukey = $this->totalRoute . (http_build_query($this->totalRouteParams, null, '_sps_'));
    }

    /**
     * @return mixed
     */
    abstract protected function getPaginator();

    /**
     * buildQuery
     *
     * @param string $param1 - if doctrineSPS ? rootModel : SQL query
     * @param string $param2 - if doctrineSPS ? rootEntityAlias : array fields for \Doctrine\ORM\Query\ResultSetMapping
     *
     * @return $this
     */
    abstract protected function buildQuery($param1, $param2);

    /**
     * @param $emName
     *
     * @return $this
     */
    public function setEmName($emName)
    {
        $this->emName = $emName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmName()
    {
        return $this->emName;
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
     * If true - remove session variable this filter|pager
     *
     * @return string|null
     */
    public function isReset()
    {
        if ($this->request->query->get('_sps_reset')) {
            $this->session->remove('_sps_filter_' . $this->ukey);
            $this->session->remove('_sps_pager_' . $this->ukey);
            $this->session->remove('_sps_sort_' . $this->ukey);

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
        return $this->doctrine->getManager($this->emName);
    }

    /**
     * @return mixed (AbstractQuery|\Doctrine\ORM\QueryBuilder)
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $alias
     * @param string $field
     * @param string $type
     * @param array $attr
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addColumn($alias, $field, $type = 'string', array $attr = array())
    {
        if (!in_array($type, $this->columnTypes)) {
            throw new InvalidArgumentException(
                sprintf("Column's type \"%s\" is not valid. Use %s", $type, implode(' or ', $this->columnTypes))
            );
        }
        $this->columns[$alias . '.' . $field] = new ColumnField($alias, $field, $type, $attr);

        return $this;
    }

    /**
     * @param string $alias
     * @param string $field
     * @param string $type
     * @param array $attr
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addFilter($alias, $field, $type = 'string', array $attr = array())
    {
        if (!in_array($type, $this->filterTypes)) {
            throw new InvalidArgumentException(
                sprintf("Filter's type \"%s\" is not valid. Use %s", $type, implode(' or ', $this->filterTypes))
            );
        }

        if (isset($attr['choices']) and $attr['choices']) {
            $attr['choices'] = $this->reconfigureChoices($attr['choices']);
        }

        if (!isset($attr['label']) and isset($this->columns[$alias . '.' . $field])) {
            $attr['label'] = $this->columns[$alias . '.' . $field]->getLabel();
        }

        $this->filters[$alias . '.' . $field] = new FilterField($alias, $field, $type, $attr);

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
     * reconfigureChoices
     *
     * @param array $choices
     * @return array $choices
     */
    protected function reconfigureChoices($choices)
    {
        if (isset($choices[0]) and is_array($choices[0])) {
            $array = array();
            foreach ($choices as $data) {
                $array[array_pop($data)] = array_shift($data);
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
     * @return void
     */
    protected function checkFilters()
    {
        if ($this->filters) {
            $this->filterForm = $this->formFactory->create(SPSType::class, null, array(
                'array_fields' => $this->filters,
                'action' => $this->router->generate($this->totalRoute, $this->totalRouteParams)
            ));
            $this->filterForm->setData($this->defaultFilters);
            if ('POST' != $this->request->getMethod()) {
                if ($this->session->has('_sps_filter_' . $this->ukey)) {
                    $data = $this->formFilterSession->unserialize('_sps_filter_' . $this->ukey, $this->emName);
                    $this->filterForm->setData($data);
                    $this->queryBuilder->buildQuery($this->filterForm, $this->query);
                } elseif (count($this->defaultFilters)) {
                    $this->queryBuilder->buildQuery($this->filterForm, $this->query);
                }
            } else {
                $this->filterForm->handleRequest($this->request);
                if ($this->filterForm->getErrors(true)->count()) {
                    $this->session->getFlashBag()->add('error', 'The form of the filter contains errors...');
                } elseif ($this->filterForm->isValid()) {
                    $this->session->remove('_sps_pager_' . $this->ukey);
                    $this->queryBuilder->buildQuery($this->filterForm, $this->query);
                    $this->formFilterSession->serialize($this->filterForm->getData(), '_sps_filter_' . $this->ukey);
                }
            }
        }
    }

    /**
     * @param $num
     */
    public function setPaginatorLimit($num)
    {
        if (is_numeric($num)) {
            $this->paginatorLimit = $num;
        }
    }

    /**
     * @param array $options
     */
    public function setPaginatorOptions(array $options)
    {
        $this->paginatorOptions = $options;
    }

    /**
     * @param $name
     * @param $value
     * @throws InvalidArgumentException
     */
    public function setOptions($name, $value)
    {
        if (!isset($this->options[$name])) {
            throw new InvalidArgumentException(sprintf("Option %s is not valid", $name));
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

        $form = $this->filterForm->createView();
        $groupField = array();
        foreach ($form->children as $key => $child) {
            $output = array();
            preg_match("/(.*)__(\d+)$/", $child->vars['name'], $output);
            if (isset($output[2])) {
                $groupField[$output[1]][] = $child;
            }
        }

        return array(
            'columns' => $this->columns,
            'group_field' => $groupField,
            'filter_form' => $form,
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
        if ($this->query instanceof NativeQuery) {
            $query = $this->query->getSQL();
        } else {
            $query = $this->query->getQuery()->getSQL();
        }

        if (null === $this->replaceRules['from'] or null === $this->replaceRules['to']) {
            return $query;
        }

        return str_replace($this->replaceRules['from'], $this->replaceRules['to'], $query);
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
