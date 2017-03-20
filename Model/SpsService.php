<?php

namespace Zk2\SpsBundle\Model;

use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Zk2\SpsBundle\Exceptions\SpsException;
use Zk2\SpsBundle\Form\Type\SpsType;
use Zk2\SpsBundle\Query\QueryBuilderBridge;
use Zk2\SpsBundle\Utils\FormFilterSerializer;
use Zk2\SpsBundle\Utils\Paginator;

class SpsService
{
    const SESSION_KEY_NAME = '_sps_qk';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var QueryBuilderBridge
     */
    protected $spsQueryBuilder;

    /**
     * @var int
     */
    protected $limitRows = 30;

    /**
     * @var array
     */
    protected $defaultSort = [];

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $emName = 'default';

    /**
     * @var SpsColumnField[]
     */
    protected $columns = [];

    /**
     * @var SpsFilterField[]
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $defaultFilters = [];

    /**
     * @var Form
     */
    protected $filterForm;

    /**
     * @var FormFilterSerializer
     */
    protected $formFilterSerializer;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $rulesForReplace = ['from' => null, 'to' => null]; // @todo: ???

    /**
     * @var string
     */
    protected $totalRoute;

    /**
     * @var array
     */
    protected $totalRouteParams = [];

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @var string
     */
    protected $sessionKeyType;

    /**
     * @var mixed
     */
    protected $autosum;

    /**
     * @param RequestStack $request
     * @param FormFactory $formFactory
     * @param Router $router
     * @param FormFilterSerializer $formFilterSerializer
     * @param string|null $sessionKeyType
     */
    public function __construct(
        RequestStack $request,
        FormFactory $formFactory,
        Router $router,
        FormFilterSerializer $formFilterSerializer,
        $sessionKeyType
    ) {
        $this->request = $request->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->formFilterSerializer = $formFilterSerializer;
        $this->totalRoute = $this->request->get('_route');
        $this->totalRouteParams = $this->request->get('_route_params');
        switch ($sessionKeyType) {
            case 'by_route':
                $this->sessionKey = $this->totalRoute.http_build_query($this->totalRouteParams, null, '_sps_');
                break;
            case 'by_query':
                $this->sessionKey = $this->request->query->get(self::SESSION_KEY_NAME);
                if ($this->sessionKey) {
                    $this->totalRouteParams = array_merge(
                        $this->totalRouteParams,
                        [self::SESSION_KEY_NAME => $this->sessionKey]
                    );
                }
                break;
            default:
                $this->sessionKey = null;
        }
        $this->sessionKeyType = $sessionKeyType;
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
            if ($this->sessionKey) {
                $this->session->remove('_sps_filter_'.$this->sessionKey);
                $this->session->remove('_sps_pager_'.$this->sessionKey);
                $this->session->remove('_sps_sort_'.$this->sessionKey);
            }

            return $this->getUrl();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function isForward()
    {
        if ('by_query' == $this->sessionKeyType and !$this->sessionKey) {
            $this->totalRouteParams = array_merge(
                $this->totalRouteParams,
                [self::SESSION_KEY_NAME => md5(microtime())]
            );

            return $this->getUrl();
        }

        return null;
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return $this->router->generate($this->totalRoute, $this->totalRouteParams);
    }

    /**
     * @param string $emName
     *
     * @return SpsService
     */
    public function setEmName($emName)
    {
        $this->emName = $emName;

        return $this;
    }

    /**
     * @param ORMQueryBuilder|DBALQueryBuilder $queryBuilder
     *
     * @return SpsService
     * @throws SpsException
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->spsQueryBuilder = new QueryBuilderBridge($this->columns, $queryBuilder);

        return $this;
    }

    /**
     * @param array $defaultSort
     *
     * @return SpsService
     */
    public function setDefaultSort(array $defaultSort)
    {
        $this->defaultSort = $defaultSort;

        return $this;
    }

    /**
     * @param int $limitRows
     */
    public function setLimitRows($limitRows)
    {
        $this->limitRows = (integer)$limitRows;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $attr
     * @return SpsService
     */
    public function addColumn($name, $type = 'string', array $attr = [])
    {
        $column = new SpsColumnField($name, $type, $attr);
        if ('by_query' == $this->sessionKeyType and $this->sessionKey) {
            $column->setSessionKey($this->sessionKey);
        }
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $attr
     * @return SpsService
     */
    public function addFilter($name, $type = 'string', array $attr = [])
    {
        if (isset($attr['choices']) and $attr['choices']) {
            $attr['choices'] = $this->reconfigureChoices($attr['choices']);
        }

        if (!isset($attr['label']) and $column = $this->getColumnByFieldName($name)) {
            $attr['label'] = $column->getLabel();
        }

        $this->filters[] = new SpsFilterField($name, $type, $attr);

        return $this;
    }

    /**
     * reconfigureChoices
     *
     * @param array $choices
     * @return array $choices
     */
    private function reconfigureChoices($choices)
    {
        if (isset($choices[0]) and is_array($choices[0])) {
            $array = [];
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
    private function checkFilters()
    {
        if ($this->filters) {
            $this->filterForm = $this->formFactory->create(
                SpsType::class,
                null,
                [
                    'array_fields' => $this->filters,
                    'action' => $this->router->generate($this->totalRoute, $this->totalRouteParams),
                ]
            );
            $this->filterForm->setData($this->defaultFilters);

            if ('POST' != $this->request->getMethod()) {
                if ($this->sessionKey and $this->session->has('_sps_filter_'.$this->sessionKey)) {
                    $data = $this->formFilterSerializer->unserialize('_sps_filter_'.$this->sessionKey, $this->emName);
                    $this->filterForm->setData($data);
                }
            } else {
                $this->filterForm->handleRequest($this->request);
                if ($this->filterForm->getErrors(true)->count()) {
                    $this->session->getFlashBag()->add('error', 'The form of the filter contains errors...');
                } elseif ($this->filterForm->isValid()) {
                    if ($this->sessionKey) {
                        $this->session->remove('_sps_pager_'.$this->sessionKey);
                        $this->formFilterSerializer->serialize(
                            $this->filterForm->getData(),
                            '_sps_filter_'.$this->sessionKey
                        );
                    }
                }
            }
            $this->spsQueryBuilder->buildQueryConditions($this->filterForm);
        }
        $this->spsQueryBuilder->buildQuery();
    }

    private function getPaginator()
    {
        $page = $this->request->query->getInt('page', 1);
        $sort = [];

        if ($this->sessionKey) {
            if ($this->request->query->has('sort')) {
                $this->session->remove('_sps_pager_'.$this->sessionKey);
                $this->session->set(
                    '_sps_sort_'.$this->sessionKey,
                    [
                        '_sps_sort_field_name' => $this->request->query->get('sort'),
                        '_sps_sort_direction' => $this->request->query->get('direction', 'asc'),
                    ]
                );
            }

            if ($this->request->query->has('page')) {
                $this->session->set('_sps_pager_'.$this->sessionKey, $page);
            } elseif ($this->session->has('_sps_pager_'.$this->sessionKey)) {
                $page = $this->session->get('_sps_pager_'.$this->sessionKey);
            }
        }

        if ($this->sessionKey and $spsSort = $this->session->get('_sps_sort_'.$this->sessionKey)) {
            $sortField = $spsSort['_sps_sort_field_name'];
            $sortType = $spsSort['_sps_sort_direction'];
            $this->getColumnByFieldName($sortField)->setSortType($sortType);
            $sort = [$sortField => $sortType];
        } elseif ($this->defaultSort) {
            $sort = $this->defaultSort;
        }

        $this->spsQueryBuilder->addOrderBy($sort);

        $paginator = new Paginator($this->spsQueryBuilder, $page, $this->limitRows);
        $paginator->setUsedRoute($this->totalRoute);
        $paginator->setUsedRouteParams($this->totalRouteParams);

        return $paginator;
    }

    /**
     * @param string $name
     * @return SpsColumnField
     * @throws SpsException
     */
    private function getColumnByFieldName($name)
    {
        foreach ($this->columns as $column) {
            if ($name == $column->getName()) {
                return $column;
            }
        }

        throw new SpsException(sprintf('Column by name "%s" not found', $name));
    }

    /**
     * Build Result
     *
     * @return array
     */
    public function buildResult()
    {
        $filter = $fields = [];
        $form = null;
        $this->checkFilters();
        if ($this->filters) {
            $form = $this->filterForm->createView();
            foreach ($form->children as $child) {
                $output = [];
                preg_match("/(.*)__(\d+)$/", $child->vars['name'], $output);
                if (isset($output[2])) {
                    $fields[$output[1]][] = $child->vars['name'];
                }
            }
            $filter = [
                'fields' => $fields,
                'form' => $form,
            ];
        }
//dump($filter, $this->getPaginator());exit;
        return [
            'filter' => $filter,
            'paginator' => $this->getPaginator(),
            'autosum' => [],//$this->spsQueryBuilder->getAutosum(),
        ];
    }
}
