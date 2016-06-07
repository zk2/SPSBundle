<?php

namespace Zk2\SPSBundle\Utils;


/**
 * Class DateRange
 * @package Zk2\SPSBundle\Utils
 */
class DateRange implements \Serializable
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
        return array(
            'start' => $this->start,
            'end' => $this->end
        );
    }

    /**
     * @param array $dates
     * @return static
     */
    public static function fromArray(array $dates)
    {
        if (!$dates['start'] instanceof \DateTime || !$dates['end'] instanceof \DateTime) {
            throw new \InvalidArgumentException('From and to are required');
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
        $data = array(
            $this->start->format(\DateTime::ATOM),
            $this->end->format(\DateTime::ATOM)
        );

        return serialize($data);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        list($this->start, $this->end) = $data;
        $this->start = \DateTime::createFromFormat(\DateTime::ATOM, $this->start);
        $this->end = \DateTime::createFromFormat(\DateTime::ATOM, $this->end);
    }
}