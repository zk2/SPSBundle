<?php

namespace Zk2\SpsBundle\Model;


interface TdBuilderInterface
{
    /**
     * @return array
     */
    public static function getInjections();

    /**
     * @param SpsColumnField $column
     * @param array $row
     * @return string
     */
    public function getTd(SpsColumnField $column, array $row);

    /**
     * @param array $autosum
     * @param SpsColumnField[] $columns
     * @return string
     */
    public function getAutosum(array $autosum, array $columns);

    /**
     * @param string $path
     */
    public function setFullWebPath($path);
}