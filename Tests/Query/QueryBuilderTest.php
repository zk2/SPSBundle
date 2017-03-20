<?php

namespace Tests\Query;

use Symfony\Component\Form\Form;
use Tests\AbstractSpsTest;
use Zk2\SpsBundle\Query\QueryBuilderBridge;

class QueryBuilderTest extends AbstractSpsTest
{
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