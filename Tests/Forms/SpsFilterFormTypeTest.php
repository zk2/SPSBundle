<?php

namespace Tests\Forms;

use Symfony\Component\Form\Form;
use Tests\AbstractSpsTest;
use Zk2\SpsBundle\Model\DateRange;
use Zk2\SpsBundle\Model\SpsFilterField;

class SpsFilterFormTypeTest extends AbstractSpsTest
{
    public function testForm()
    {
        $spsFilterFields = $this->getSpsFilterFields();
        $form = $this->createForm($spsFilterFields);

        $children = $form->createView()->children;
        /** @var SpsFilterField $field */
        foreach ($form->getConfig()->getOption('array_fields') as $field) {
            for ($i = 0; $i < $field->getQuantity(); $i++) {
                $this->assertArrayHasKey($field->getNameForFormClass().'__'.$i, $children);
            }
        }

        $form->submit([]);
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $form = $this->createForm($spsFilterFields);
        $arrayData = $this->getArrayData();
        $form->submit($arrayData);
        $formData = $form->getData();
        $this->assertTrue($form->isSynchronized());
//        $string = var_export($this->getErrorMessages($form), true);
//        printf("%s\n", $string);
//        exit;
        $this->assertTrue($form->isValid());
        $this->assertEquals($formData['country_name__0']['name'], 'Ukraine');
        $this->assertEquals($formData['country_lastDate__0']['name'], new \DateTime('2010-01-01'));
        $this->assertInstanceOf(DateRange::class, $formData['country_date__0']['name']);

        $form = $this->createForm($spsFilterFields);
        $arrayData['country_population__0']['name'] = 'la-la-la';
        $arrayData['country_lastDate__0']['name'] = 'ho-ho-ho';
        $arrayData['country_date__0']['name'] = ['start' => '2010-01-01', 'end' => null];
        $form->submit($arrayData);
        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertEquals($form->getErrors(true, false)->count(), 3);
    }

    /**
     * @param array SpsFilterField[]
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createForm(array $arrayFields)
    {
        return $this->factory->create(
            'Zk2\SpsBundle\Form\Type\SpsType',
            null,
            ['array_fields' => $arrayFields]
        );
    }

    private function getErrorMessages(Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
        /** @var Form $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}