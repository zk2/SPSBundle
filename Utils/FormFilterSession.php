<?php
namespace Zk2\SPSBundle\Utils;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Service serialized / unserialized form filter in the user's session
 */
class FormFilterSession
{
    protected $doctrine, $session;

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
     * If value instanseof Object:
     * - if value instanseof DateTime -- set DateTime->format('Y-m-d')
     * - else if value instanseof DateRange -- DateRange->serialize()
     * - else set sprintf( "CLASS %s %u",get_class(Object), Object->getId() )
     */
    public function serialize($data, $filter_name)
    {
        if (!$data) {
            return null;
        }
        foreach ($data as $fname => $field) {
            if (isset($field['name']) and is_object($field['name'])) {
                if ($field['name'] instanceof \DateTime) {
                    $data[$fname]['name'] = $field['name']->format('Y-m-d');
                } elseif ($field['name'] instanceof DateRange) {
                    $data[$fname]['name'] = 'DateRange :: ' . $field['name']->serialize();
                } else {
                    $entity = $field['name'];
                    $data[$fname]['name'] = sprintf("CLASS %s %u", get_class($entity), $entity->getId());
                }
            }
        }
        $this->session->set($filter_name, $data);
    }

    /**
     * unserialize
     *
     * - If preg_match("/^(CLASS)\s(.*)\s(\d+)$/", $value) -- find and set Object
     * - Else if DateRange -- set DateRange->unserialize
     * - Else if checkdate == true -- set DateTime
     * - Else set data
     *
     * @return array formData
     */
    public function unserialize($filter_name, $em_name = 'default')
    {
        $entity_manager = $this->doctrine->getManager($em_name);
        $data = $this->session->get($filter_name);
        foreach ($data as $fname => $field) {
            $date = date_parse($field['name']);
            $output = array();
            if (preg_match("/^(CLASS)\s(.*)\s(\d+)$/", $field['name'], $output)) {
                $entity = $entity_manager->find($output[2], $output[3]);
                if (is_object($entity)) {
                    $data[$fname]['name'] = $entity;
                } else {
                    unset($data[$fname]);
                }
            } elseif (strpos($field['name'], 'DateRange :: ') === 0) {
                $str = str_replace('DateRange :: ', '', $field['name']);
                $dateRange = new DateRange();
                $dateRange->unserialize($str);
                $data[$fname]['name'] = $dateRange;
            } elseif ($date and checkdate($date["month"], $date["day"], $date["year"])) {
                $data[$fname]['name'] = new \DateTime($field['name']);
            }
        }

        return $data;
    }

}
