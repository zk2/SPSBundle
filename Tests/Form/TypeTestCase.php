<?php

namespace Zk2\SPSBundle\Tests\Form;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase as BaseTypeTestCase;

/**
 * Class TypeTestCase
 */
abstract class TypeTestCase extends BaseTypeTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtensions($this->getTypeExtensions())
            ->getFormFactory();

        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function getTypeExtensions()
    {
        return array();
    }
}
