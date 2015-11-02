<?php

namespace Zk2\SPSBundle\Tests\Form;

use Zk2\SPSBundle\Form\Filter\BooleanFilterType;
use Zk2\SPSBundle\Form\Filter\ChoiceFilterType;
use Zk2\SPSBundle\Form\Filter\DateFilterType;
use Zk2\SPSBundle\Form\Filter\NumericFilterType;
use Zk2\SPSBundle\Form\Filter\TextFilterType;
use Zk2\SPSBundle\Form\Type\DateBootstrapType;
use Zk2\SPSBundle\Form\Type\SPSType;
use Zk2\SPSBundle\Model\FilterField;
use Symfony\Component\Form\PreloadedExtension;
use Zk2\SPSBundle\Utils\ConditionOperator;

class RegistrationFormTypeTest extends ValidatorExtensionTypeTestCase
{
    protected function getExtensions()
    {
        $types = array(
            new NumericFilterType(),
            new BooleanFilterType(),
            new ChoiceFilterType(),
            new DateFilterType(),
            new TextFilterType('text'),
            new TextFilterType('string'),
            new DateBootstrapType('en'),
        );

        $preloads = array();
        foreach($types as $type){
            $preloads[$type->getName()] = $type;
        }

        return array(new PreloadedExtension($preloads, array()));
    }

    /**
     * @expectedException \Zk2\SPSBundle\Exceptions\InvalidArgumentException
     */
    public function testCreate()
    {
        $data = range(1,5);
        $this->factory->create(new SPSType($data), array());
    }

    public function testSubmit()
    {
        $data = $this->getFilterFields();
        $formData = $this->getFormData();
        $form = $this->factory->create(new SPSType($data), array());
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        //print_r($form->get('m__id0')->getConfig()->getOptions());
        $this->assertEquals('=', ConditionOperator::getOperator($form->get('m__id0')->getData()['condition_operator']));
        $this->assertEquals('281', $form->getData()['m__id0']['name']);
        $this->assertFalse('281 ' == $form->getData()['m__id0']['name']);
        $this->assertFalse(isset($form->getData()['m__id1']));
        $this->assertFalse(isset($form->getData()['m__id0']['condition_pattern']));

        $this->assertEquals('NOT LIKE', ConditionOperator::getOperator($form->get('m__name0')->getData()['condition_operator']));
        $this->assertEquals('LIKE', ConditionOperator::getOperator($form->get('m__name1')->getData()['condition_operator']));
        $this->assertEquals('abcde', $form->getData()['m__name0']['name']);
        $this->assertTrue(isset($form->getData()['m__name1']));
        $this->assertTrue(isset($form->getData()['m__name1']['condition_pattern']));
        $this->assertTrue(isset($form->getData()['m__name2']['condition_pattern']));
        $this->assertFalse(isset($form->getData()['m__name3']));

        $this->assertEquals('LIKE', ConditionOperator::getOperator($form->get('m__title0')->getData()['condition_operator']));
        $this->assertEquals('LIKE', ConditionOperator::getOperator($form->get('m__title1')->getData()['condition_operator']));
        $this->assertEquals('Mustang', $form->getData()['m__title0']['name']);
        $this->assertTrue(isset($form->getData()['m__title1']));
        $this->assertTrue(isset($form->getData()['m__title1']['condition_pattern']));
        $this->assertFalse(isset($form->getData()['m__title2']));
        $this->assertFalse(isset($form->getData()['m__title3']));

        $this->assertEquals('=', ConditionOperator::getOperator($form->get('m__color0')->getData()['condition_operator']));
        $this->assertEquals('blue', $form->getData()['m__color0']['name']);
        $this->assertFalse('blue ' == $form->getData()['m__color0']['name']);
        $this->assertFalse(isset($form->getData()['m__color1']));
        $this->assertFalse(isset($form->getData()['m__color0']['condition_pattern']));

        $this->assertEquals('=', ConditionOperator::getOperator($form->get('m__airbag0')->getData()['condition_operator']));
        $this->assertEquals('1', $form->getData()['m__airbag0']['name']);
        $this->assertFalse('2' == $form->getData()['m__airbag0']['name']);
        $this->assertFalse(isset($form->getData()['m__airbag1']));
        $this->assertFalse(isset($form->getData()['m__airbag0']['condition_pattern']));

        $this->assertEquals('=', ConditionOperator::getOperator($form->get('m__dateView0')->getData()['condition_operator']));
        $this->assertEquals('<', ConditionOperator::getOperator($form->get('m__dateView1')->getData()['condition_operator']));
        $this->assertEquals(new \DateTime('2014-12-13'), $form->getData()['m__dateView0']['name']);
        $this->assertEquals(new \DateTime('2014-10-01'), $form->getData()['m__dateView1']['name']);
        $this->assertTrue(isset($form->getData()['m__dateView1']));
        $this->assertTrue(isset($form->getData()['m__dateView1']['condition_pattern']));
        $this->assertFalse(isset($form->getData()['m__dateView2']['condition_pattern']));
        $this->assertFalse(isset($form->getData()['m__dateView3']));
    }

    protected function getFormData()
    {
        return array(
            'm__id0' => array(
                'condition_operator' => '%s', // =
                'name' => '281',
            ),
            'm__name0' => array(
                'condition_operator' => "x%s%%", //  NOT LIKE%
                'name' => 'abcde',
            ),
            'm__name1' => array(
                'condition_pattern' => 'AND',
                'condition_operator' => '%%%s%%', // %LIKE%
                'name' => 'bcd',
            ),
            'm__name2' => array(
                'condition_pattern' => 'AND',
                'condition_operator' => 'IS NOT NULL',
                'name' => '',
            ),
            'm__title0' => array(
                'condition_operator' => '%%%s%%', // %LIKE%
                'name' => 'Mustang',
            ),
            'm__title1' => array(
                'condition_pattern' => 'OR',
                'condition_operator' => '%%%s', // %LIKE
                'name' => 'Rio',
            ),
            'm__color0' => array(
                'condition_operator' => '%s',
                'name' => 'blue',
            ),
            'm__airbag0' => array(
                'condition_operator' => '%s',
                'name' => '1',
            ),
            'm__dateView0' => array(
                'condition_operator' => '%s',
                'name' => '2014-12-13',
            ),
            'm__dateView1' => array(
                'condition_pattern' => 'OR',
                'condition_operator' => 'xxx%s', // <
                'name' => '2014-10-01',
            ),
        );
    }

    protected function getFilterFields()
    {
        return array(
            new FilterField('m', 'id', 'numeric', array()),
            new FilterField('m', 'name', 'string', array('quantity' => 3)),
            new FilterField('m', 'title', 'text', array('quantity' => 2)),
            new FilterField(
                'm', 'color', 'choice', array(
                    'choices' => array(
                        'black' => 'black',
                        'blue' => 'blue',
                        'brown' => 'brown',
                        'green' => 'green',
                        'red' => 'red',
                        'silver' => 'silver',
                        'white' => 'white',
                        'yellow' => 'yellow',
                    ),
                )
            ),
            new FilterField('m', 'airbag', 'boolean', array()),
            new FilterField('m', 'dateView', 'date', array('quantity' => 2,)),
        );
    }
}
