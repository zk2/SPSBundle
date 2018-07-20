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

namespace Tests;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Test\TypeTestCase;
use Zk2\SpsBundle\Model\SpsFilterField;

/**
 * Class AbstractSpsTest
 */
abstract class AbstractSpsTest extends TypeTestCase
{
    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @return SpsFilterField[]
     */
    protected function getSpsFilterFields()
    {
        return [
            new SpsFilterField('country.name', 'string', ['quantity' => 3]),
            new SpsFilterField('country.population', 'numeric', ['label' => 'Population', 'quantity' => 2]),
            new SpsFilterField('country.lastDate', 'date', ['quantity' => 2]),
            new SpsFilterField('country.date', 'dateRange', ['quantity' => 2]),
            new SpsFilterField('country.isDepend', 'boolean', ['quantity' => 2]),
            new SpsFilterField(
                'country.continent',
                'choice',
                [
                    'quantity' => 2,
                    'choices'  => [
                        [['id' => 1, 'name' => 'Africa']],
                        [['id' => 2, 'name' => 'Antarctica']],
                        [['id' => 3, 'name' => 'Asia']],
                        [['id' => 4, 'name' => 'Europe']],
                        [['id' => 5, 'name' => 'North America']],
                        [['id' => 6, 'name' => 'Oceania']],
                        [['id' => 7, 'name' => 'South America']],
                    ],
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getArrayData()
    {
        return [
            'country_name__0'       => [
                'comparison_operator' => 'equals',
                'name'                => 'Ukraine',
            ],
            'country_name__1'       => [
                'boolean_operator'    => 'OR',
                'comparison_operator' => 'beginsWith',
                'name'                => 'Germ',
            ],
            'country_population__0' => [
                'comparison_operator' => 'greaterThan',
                'name'                => 100000,
            ],
            'country_population__1' => [
                'boolean_operator'    => 'AND',
                'comparison_operator' => 'lessThan',
                'name'                => 1000000,
            ],
            'country_lastDate__0'   => [
                'comparison_operator' => 'greaterThanOrEqual',
                'name'                => '2010-01-01',
            ],
            'country_lastDate__1'   => [
                'boolean_operator'    => 'AND',
                'comparison_operator' => 'lessThanOrEqual',
                'name'                => '2015-01-01',
            ],
            'country_date__0'       => [
                'comparison_operator' => 'between',
                'name'                => ['start' => '2010-01-01', 'end' => '2015-01-01'],
            ],
            'country_isDepend__0'   => [
                'comparison_operator' => 'equals',
                'name'                => true,
            ],
            'country_continent__0'  => [
                'comparison_operator' => 'equals',
                'name'                => 3,
            ],
            'country_continent__1'  => [
                'boolean_operator'    => 'OR',
                'comparison_operator' => 'equals',
                'name'                => 5,
            ],
        ];
    }
}