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

namespace Zk2\SpsBundle\Model;

/**
 * Interface TdBuilderInterface
 */
interface TdBuilderInterface
{
    /**
     * @return array
     */
    public static function getInjections();

    /**
     * @param SpsColumnField $column
     * @param array          $row
     *
     * @return string
     */
    public function getTd(SpsColumnField $column, array $row);

    /**
     * @param array            $autosum
     * @param SpsColumnField[] $columns
     *
     * @return string
     */
    public function getAutosum(array $autosum, array $columns);

    /**
     * @param string $path
     */
    public function setFullWebPath($path);
}
