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

namespace Tests\Query;

use Symfony\Component\Form\Form;
use Tests\AbstractSpsTest;
use Zk2\SpsBundle\Query\QueryBuilderBridge;

/**
 * Class QueryBuilderTest
 */
class QueryBuilderTest extends AbstractSpsTest
{
    /**
     * testConditionsQueryBuilder
     */
    public function testConditionsQueryBuilder()
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $ormQb */
        $ormQb = $this->createMock('Doctrine\DBAL\Query\QueryBuilder');
        $columns = $this->getSpsFilterFields();
        $DBALQueryBuilder = new QueryBuilderBridge($columns, $ormQb);
        /** @var Form $form */
        $form = $this->factory->create('Zk2\SpsBundle\Form\Type\SpsType', null, ['array_fields' => $columns]);
        $form->submit($this->getArrayData());
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $DBALQueryBuilder->buildQueryConditions($form);
    }
}