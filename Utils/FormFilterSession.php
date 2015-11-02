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
            if (preg_match("/^(CLASS)\s(.*)\s(\d+)$/", $field['name'], $array)) {
                $entity = $entity_manager->find($array[2], $array[3]);
                if (is_object($entity)) {
                    $data[$fname]['name'] = $entity;
                } else {
                    unset($data[$fname]);
                }
            } elseif ($date and checkdate($date["month"], $date["day"], $date["year"])) {
                $data[$fname]['name'] = new \DateTime($field['name']);
            }
        }

        return $data;
    }

}
