<?php

namespace Zk2\SPSBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zk2\SPSBundle\Utils\DateRange;


/**
 * Class DateRangeToArrayTransformer
 * @package Zk2\SPSBundle\Form\DataTransformer
 */
class DateRangeToArrayTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($dateRange)
	{
		if ($dateRange === null) return null;

		if((!$dateRange instanceof DateRange)) {
			throw new TransformationFailedException(
				sprintf('%s expects %s instance as input', get_class($this), DateRange::class)
			);
		}

		return $dateRange->toArray();
	}

    /**
     * @inheritdoc
     */
    public function reverseTransform($array)
	{
		if ($array === null) return null;

		try {
			$dateRange = DateRange::fromArray($array);
		} catch(\InvalidArgumentException $e) {
			return null;
		}  catch(\LogicException $e) {
			throw new TransformationFailedException();
		}

		return $dateRange;
	}

}