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

namespace Zk2\SpsBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;
use Zk2\SpsBundle\Exceptions\SpsException;
use Zk2\SpsBundle\Model\SpsColumnField;
use Zk2\SpsBundle\Model\TdBuilderInterface;
use Zk2\SpsBundle\Utils\Paginator;

/**
 * Class that extends the Twig_Extension
 */
class SpsExtension extends AbstractExtension
{
    /**
     * @var TdBuilderInterface|null
     */
    protected $tdService;

    /**
     * @var array
     */
    protected $options;

    /**
     * SpsExtension constructor.
     *
     * @param TdBuilderInterface|null $tdService
     * @param array                   $options
     */
    public function __construct(TdBuilderInterface $tdService = null, array $options = [])
    {
        $this->tdService = $tdService;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        if ($this->tdService) {
            return [
                new TwigFunction(
                    'sps_filter_form',
                    [$this, 'filter'],
                    ['is_safe' => ['html'], 'needs_environment' => true]
                ),
                new TwigFunction(
                    'sps_build_td',
                    [$this, 'td'],
                    ['is_safe' => ['html'], 'needs_environment' => false]
                ),
                new TwigFunction(
                    'sps_pagination_sortable',
                    [$this, 'sortable'],
                    ['is_safe' => ['html'], 'needs_environment' => true]
                ),
                new TwigFunction(
                    'sps_pagination_pagination',
                    [$this, 'pagination'],
                    ['is_safe' => ['html'], 'needs_environment' => true]
                ),
                new TwigFunction(
                    'sps_filter_table',
                    [$this, 'table'],
                    ['is_safe' => ['html'], 'needs_environment' => true]
                ),
                new TwigFunction(
                    'sps_build_autosum',
                    [$this, 'autosum'],
                    ['is_safe' => ['html'], 'needs_environment' => false]
                ),
            ];
        }

        return [];
    }

    /**
     * @param SpsColumnField $column
     * @param array          $row
     *
     * @return string
     *
     * @throws SpsException
     */
    public function td(SpsColumnField $column, array $row)
    {
        if (!$this->tdService) {
            throw new SpsException('Service "td_builder_service_class" is not defined in config.yml');
        }

        return $this->tdService->getTd($column, $row);
    }

    /**
     * @param array            $autosum
     * @param SpsColumnField[] $columns
     *
     * @return string
     *
     * @throws SpsException
     */
    public function autosum(array $autosum, array $columns)
    {
        if (!$this->tdService) {
            throw new SpsException('Service "td_builder_service_class" is not defined in config.yml');
        }

        return $this->tdService->getAutosum($autosum, $columns);
    }

    /**
     * @param Environment $env
     * @param array       $filter
     * @param int         $colspan
     *
     * @return string
     */
    public function filter(Environment $env, array $filter, $colspan = 2)
    {
        return $env->render(
            $this->options['filter_template'],
            ['filter' => $filter, 'colspan' => $colspan]
        );
    }

    /**
     * @param Environment    $env
     * @param SpsColumnField $column
     * @param array          $usedRoute
     * @param array          $usedRouteParams
     *
     * @return string
     */
    public function sortable(Environment $env, SpsColumnField $column, $usedRoute, array $usedRouteParams)
    {
        return $env->render(
            $this->options['sortable_template'],
            ['column' => $column, 'usedRoute' => $usedRoute, 'usedRouteParams' => $usedRouteParams]
        );
    }

    /**
     * @param Environment $env
     * @param Paginator   $paginator
     *
     * @return string
     */
    public function pagination(Environment $env, Paginator $paginator)
    {
        return $env->render(
            $this->options['pagination_template'],
            ['paginator' => $paginator]
        );
    }

    /**
     * @param Environment $env
     * @param Paginator   $paginator
     * @param array       $autosum
     *
     * @return string
     */
    public function table(Environment $env, Paginator $paginator, array $autosum = [])
    {
        return $env->render(
            $this->options['table_template'],
            ['paginator' => $paginator, 'autosum' => $autosum]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zk2.sps.twig_extension';
    }
}
