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

namespace Zk2\SpsBundle\Utils;

use Traversable;
use Zk2\SpsBundle\Exceptions\SpsException;
use Zk2\SpsBundle\Model\SpsColumnField;
use Zk2\SpsBundle\Query\QueryBuilderBridge;

/**
 * The paginator
 */
class Paginator implements \Countable, \IteratorAggregate
{
    /**
     * @var QueryBuilderBridge
     */
    private $queryBuilder;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $pageRange = 5;

    /**
     * @var string
     */
    private $usedRoute;

    /**
     * @var array
     */
    private $usedRouteParams = [];

    /**
     * @var int
     */
    private $numItemsPerPage;

    /**
     * Paginator constructor.
     *
     * @param QueryBuilderBridge $queryBuilder
     * @param int                $page
     * @param int                $numItemsPerPage
     */
    public function __construct(QueryBuilderBridge $queryBuilder, $page = 1, $numItemsPerPage = 30)
    {
        $this->queryBuilder = $queryBuilder;
        $this->page = $page;
        $this->numItemsPerPage = $numItemsPerPage;
    }

    /**
     * @return SpsColumnField[]
     */
    public function getColumns()
    {
        return $this->queryBuilder->getColumns();
    }

    /**
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setCurrentPageNumber($page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getNumItemsPerPage()
    {
        return $this->numItemsPerPage;
    }

    /**
     * @param int $numItemsPerPage
     *
     * @throws SpsException
     */
    public function setNumItemsPerPage($numItemsPerPage)
    {
        if ($numItemsPerPage < 1) {
            throw new SpsException('NumItemsPerPage must be more zero');
        }
        $this->numItemsPerPage = $numItemsPerPage;
    }

    /**
     * @return int
     */
    public function getCountPages()
    {
        return (int) ceil($this->count() / $this->numItemsPerPage);
    }

    /**
     * @param string $usedRoute -- route name
     */
    public function setUsedRoute($usedRoute)
    {
        $this->usedRoute = $usedRoute;
    }

    /**
     * @return string
     */
    public function getUsedRoute()
    {
        return $this->usedRoute;
    }

    /**
     * @return array
     */
    public function getUsedRouteParams()
    {
        return $this->usedRouteParams;
    }

    /**
     * @param array $usedRouteParams -- route params
     */
    public function setUsedRouteParams(array $usedRouteParams)
    {
        $this->usedRouteParams = $usedRouteParams;
    }

    /**
     * @return array
     */
    public function getPaginationData()
    {
        $pageCount = $this->getCountPages();
        $current = $this->page;

        if ($pageCount < $current) {
            $this->page = $current = $pageCount;
        }

        if ($this->pageRange > $pageCount) {
            $this->pageRange = $pageCount;
        }

        $delta = ceil($this->pageRange / 2);

        if ($current - $delta > $pageCount - $this->pageRange) {
            $pages = range($pageCount - $this->pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $this->pageRange);
        }

        $proximity = floor($this->pageRange / 2);

        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $viewData = [
            'last'            => $pageCount,
            'current'         => $current,
            'numItemsPerPage' => $this->numItemsPerPage,
            'first'           => 1,
            'pageCount'       => $pageCount,
            'totalCount'      => $this->count(),
            'pageRange'       => $this->pageRange,
            'startPage'       => $startPage,
            'endPage'         => $endPage,
            'previous'        => null,
            'next'            => null,
        ];

        if ($current - 1 > 0) {
            $viewData['previous'] = $current - 1;
        }

        if ($current + 1 <= $pageCount) {
            $viewData['next'] = $current + 1;
        }

        $viewData['pagesInRange'] = $pages;
        $viewData['firstPageInRange'] = min($pages);
        $viewData['lastPageInRange'] = max($pages);

        return $viewData;
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     *
     * @since 5.0.0
     *
     * @throws SpsException
     */
    public function getIterator()
    {
        $offset = $this->numItemsPerPage * ($this->page - 1);
        $results = $this->queryBuilder->getResult($this->numItemsPerPage, $offset);

        return new \ArrayIterator($results);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer. The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return $this->queryBuilder->count();
    }
}
