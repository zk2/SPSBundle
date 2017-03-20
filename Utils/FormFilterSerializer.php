<?php
namespace Zk2\SpsBundle\Utils;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;
use Zk2\SpsBundle\Model\DateRange;

/**
 * Service serialized / unserialized form filter in the user's session
 */
class FormFilterSerializer
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Registry $doctrine
     * @param Session $session
     */
    public function __construct(Registry $doctrine, Session $session)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    /**
     * serialize
     *
     * @param array $data
     * @param string $filterName
     */
    public function serialize($data, $filterName)
    {
        if ($data) {
            foreach ($data as $filedName => $field) {
                if (isset($field['name']) and is_object($field['name'])) {
                    if ($field['name'] instanceof \DateTime) {
                        $data[$filedName]['name'] = $field['name']->format('Y-m-d');
                    } elseif ($field['name'] instanceof DateRange) {
                        $data[$filedName]['name'] = 'DateRange :: ' . $field['name']->serialize();
                    } else {
                        $entity = $field['name'];
                        $data[$filedName]['name'] = sprintf("CLASS %s %u", get_class($entity), $entity->getId());
                    }
                }
            }
            $this->session->set($filterName, $data);
        }
    }

    /**
     * unserialize
     *
     * @param string $filterName
     * @param string $emName
     *
     * @return array formData
     */
    public function unserialize($filterName, $emName = 'default')
    {
        $entity_manager = $this->doctrine->getManager($emName);
        $data = $this->session->get($filterName);
        foreach ($data as $filedName => $field) {
            $date = date_parse($field['name']);
            $output = [];
            if (preg_match("/^(CLASS)\s(.*)\s(\d+)$/", $field['name'], $output)) {
                $entity = $entity_manager->find($output[2], $output[3]);
                if (is_object($entity)) {
                    $data[$filedName]['name'] = $entity;
                } else {
                    unset($data[$filedName]);
                }
            } elseif (strpos($field['name'], 'DateRange :: ') === 0) {
                $str = str_replace('DateRange :: ', '', $field['name']);
                $dateRange = new DateRange();
                $dateRange->unserialize($str);
                $data[$filedName]['name'] = $dateRange;
            } elseif ($date and checkdate($date["month"], $date["day"], $date["year"])) {
                $data[$filedName]['name'] = new \DateTime($field['name']);
            }
        }

        return $data;
    }
}
