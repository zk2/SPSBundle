<?php
/**
 * This file is part of the SpsBundle.
 *
 * (c) Evgeniy Budanov <budanov.ua@gmail.comm> 2017.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Zk2\SpsBundle\Query;

use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Zk2\SpsBundle\Exceptions\SpsException;
use Zk2\SpsBundle\Model\SpsColumnField;
use Zk2\SpsComponent\Condition\ConditionInterface;
use Zk2\SpsComponent\Condition\Container;
use Zk2\SpsComponent\Condition\ContainerInterface;
use Zk2\SpsComponent\QueryBuilderInterface;
use Zk2\SpsComponent\QueryBuilderFactory;

/**
 * Class QueryBuilderBridge
 */
class QueryBuilderBridge
{
    /**
     * @var QueryBuilderInterface $queryBuilder
     */
    private $queryBuilder;

    /**
     * @var SpsColumnField[]
     */
    private $columns;

    /**
     * @var array
     */
    private $whereData = [ContainerInterface::COLLECTION_NAME => []];

    /**
     * QueryBuilderBridge constructor.
     *
     * @param SpsColumnField[]                 $columns
     * @param ORMQueryBuilder|DBALQueryBuilder $queryBuilder
     *
     * @throws \Zk2\SpsComponent\QueryBuilderException
     */
    public function __construct(array $columns, $queryBuilder)
    {
        $this->columns = $columns;
        $this->queryBuilder = QueryBuilderFactory::createQueryBuilder($queryBuilder);
    }

    /**
     * @return SpsColumnField[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Form $form
     *
     * @return void
     *
     * @throws SpsException
     */
    public function buildQueryConditions(Form $form)
    {
        $groupChild = [];

        /** @var Form $child */
        foreach ($form->all() as $child) {
            $key = $child->getConfig()->getOption('sps_filter_field');
            if (null === $key) {
                throw new SpsException('Option sps_filter_field is not defined');
            }
            $groupChild[$key][] = $child;
        }

        foreach ($groupChild as $children) {
            if ($collection = $this->applyFilter($children)) {
                $this->whereData[ContainerInterface::COLLECTION_NAME][] = [
                    ContainerInterface::AND_OR_OPERATOR_NAME => ContainerInterface::OPERATOR_AND,
                    ContainerInterface::COLLECTION_NAME => $collection,
                ];
            }
        }
    }

    /**
     * @return void
     *
     * @throws \Zk2\SpsComponent\Condition\ContainerException
     */
    public function buildQuery()
    {
        $container = Container::create($this->whereData);
        $this->queryBuilder->buildWhere($container);
    }

    /**
     * @param array $orderBy
     *
     * @return QueryBuilderInterface
     *
     * @throws \Exception
     */
    public function addOrderBy(array $orderBy)
    {
        $ob = [];
        foreach ($orderBy as $field => $type) {
            try {
                $column = $this->getColumnByFieldName($field);
                $column->setSortType($type);
                $ob[] = [$field, $type, $column->getAttr('sort_function')];
            } catch (SpsException $e) {
                $ob[] = [$field, $type];
            } catch (\Exception $e) {
                throw $e;
            }
        }
        $this->queryBuilder->buildOrderBy($ob);

        return $this->queryBuilder;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->queryBuilder->totalResultCount();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     *
     * @throws SpsException
     */
    public function getResult($limit, $offset)
    {
        $results = $this->queryBuilder->getResult($limit, $offset);

        if (count($results) && is_object(current($results))) {
            $from = $join = [];
            /** @var From $item */
            foreach ($this->queryBuilder->getSqlPart($this->queryBuilder->getQueryBuilder(), 'from') as $item) {
                $from[$item->getAlias()] = $item->getFrom();
            }
            foreach ($this->queryBuilder->getSqlPart($this->queryBuilder->getQueryBuilder(), 'join') as $root => $joins) {
                /** @var Join $item */
                foreach ($joins as $item) {
                    $join[$item->getAlias()] = $item->getJoin();
                }
            }
            $newResults = [];
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($results as $result) {
                $res = [];
                foreach ($this->columns as $column) {
                    if (isset($from[$column->getAlias()])) {
                        $this->isReadable($accessor, $result, $column->getField());
                        $res[$column->getName()] = $accessor->getValue($result, $column->getField());
                        if ($column->getAttr('link')) {
                            foreach ($column->getAttr('link', 'route_params', []) as $prop => $val) {
                                if ($accessor->isReadable($result, $val)) {
                                    $res[$column->getAliasAndDotOrNull().$val] = $accessor->getValue($result, $val);
                                }
                            }
                        }
                    } elseif (isset($join[$column->getAlias()])) {
                        $joinObj = $this->getValue($join, $column->getAlias(), $result);
                        //if ($joinObj instanceof \Doctrine\Common\Collections\Collection) {$joinObj = $joinObj->first();}
                        if ($joinObj) {
                            $this->isReadable($accessor, $joinObj, $column->getField());
                            $res[$column->getName()] = $accessor->getValue($joinObj, $column->getField());
                            if ($column->getAttr('link')) {
                                foreach ($column->getAttr('link', 'route_params', []) as $prop => $val) {
                                    if ($accessor->isReadable($joinObj, $val)) {
                                        $res[$column->getAliasAndDotOrNull().$val] = $accessor->getValue(
                                            $joinObj,
                                            $val
                                        );
                                    }
                                }
                            }
                        } else {
                            $res[$column->getName()] = null;
                        }
                    }
                }
                $newResults[] = $res;
            }
            $results = $newResults;
        }

        return $results;
    }

    /**
     * @param array  $join
     * @param string $alias
     * @param mixed  $result
     *
     * @return mixed|null
     *
     * @throws SpsException
     */
    private function getValue(array $join, string $alias, $result)
    {
        $arr = explode('.', $join[$alias]);

        if (2 !== count($arr)) {
            throw new SpsException(sprintf('Bad alias "%s"', $alias));
        }

        if (isset($join[$arr[0]])) {
            $result = $this->getValue($join, $arr[0], $result);
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $this->isReadable($accessor, $result, $arr[1]);

        return $accessor->getValue($result, $arr[1]);
    }

    /**
     * @param PropertyAccessor $accessor
     * @param mixed            $object
     * @param string           $property
     *
     * @throws SpsException
     */
    private function isReadable(PropertyAccessor $accessor, $object, $property)
    {
        if (!$accessor->isReadable($object, $property)) {
            throw new SpsException(
                sprintf('Object "%s" don\'t has property "%s"', get_class($object), $property)
            );
        }
    }

    /**
     * @param array|Form[] $children
     *
     * @return array
     */
    private function applyFilter(array $children)
    {
        $collection = [];

        foreach ($children as $i => $child) {
            if ($child->getConfig()->getOption('not_used')) {
                continue;
            }

            $comparisonOperator = $child->get('comparison_operator')->getData();
            $value = $child->get('name')->getData();

            if (!in_array($comparisonOperator, [ConditionInterface::IS_NULL, ConditionInterface::IS_NOT_NULL])
                and !is_object($value)
                and !strlen($value = trim($value))
            ) {
                continue;
            }

            $formFieldType = $child->getConfig()->getOption('sps_filter_type');

            if ('dateRange' === $formFieldType) {
                $start = $child->get('name')->get('start')->getData();
                $end = $child->get('name')->get('end')->getData();
                if ($end and !$start) {
                    $start = clone $end;
                }
                $value = [
                    $start instanceof \DateTime ? $start->format('Y-m-d') : null,
                    $end instanceof \DateTime ? $end->format('Y-m-d') : null,
                ];
            } elseif ('date' === $formFieldType) {
                $start = $child->get('name')->getData();
                $value = $start instanceof \DateTime ? $start->format('Y-m-d') : null;
            }

            $condition = [
                'andOrOperator' => $child->has('boolean_operator') ? $child->get('boolean_operator')->getData() : null,
                'condition' => [
                    'property' => $child->getConfig()->getOption('sps_filter_field'),
                    'comparisonOperator' => $child->get('comparison_operator')->getData(),
                    'value' => $value,
                    'function' => $child->getConfig()->getOption('sps_filter_function'),
                ],
            ];
            $collection[] = $condition;
        }

        return $collection;
    }

    /**
     * @param string $name
     *
     * @return SpsColumnField
     *
     * @throws SpsException
     */
    private function getColumnByFieldName($name)
    {
        foreach ($this->columns as $column) {
            if ($name === $column->getName()) {
                return $column;
            }
        }

        throw new SpsException(sprintf('Column by name "%s" not found', $name));
    }
}
