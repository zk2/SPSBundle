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

use DateTimeInterface;

/**
 * Class DateRange
 */
class DateRange
{
    /**
     * @var \DateTime
     */
    protected $start;

    /**
     * @var \DateTime
     */
    protected $end;

    /**
     * DateRange constructor.
     *
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     */
    public function __construct(\DateTime $start = null, \DateTime $end = null)
    {
        if ($start > $end) {
            throw new \LogicException('To must be greater than from');
        }
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'start' => $this->start,
            'end'   => $this->end,
        ];
    }

    /**
     * @param array $dates
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $dates)
    {
        if (!$dates['start'] instanceof \DateTime and !$dates['end'] instanceof \DateTime) {
            return null;
        }

        return new static($dates['start'], $dates['end']);
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $data = [
            ($this->start ? $this->start->format(DateTimeInterface::ATOM) : null),
            ($this->end ? $this->end->format(DateTimeInterface::ATOM) : null),
        ];

        return serialize($data);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        [$this->start, $this->end] = $data;
        if ($this->start) {
            $this->start = \DateTime::createFromFormat(DateTimeInterface::ATOM, $this->start);
        }
        if ($this->end) {
            $this->end = \DateTime::createFromFormat(DateTimeInterface::ATOM, $this->end);
        }
    }
}
