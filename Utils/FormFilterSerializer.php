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

namespace Zk2\SpsBundle\Utils;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Zk2\SpsBundle\Model\DateRange;

/**
 * Service serialize / unserialize form filter in the user's session
 */
class FormFilterSerializer
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine
     * @param Session         $session
     */
    public function __construct(ManagerRegistry $doctrine, Session $session)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    /**
     * serialize
     *
     * @param array  $data
     * @param string $filterName
     * @param string $emName
     */
    public function serialize($data, $filterName, $emName = 'default')
    {
        if ($data) {
            foreach ($data as $filedName => $field) {
                if (isset($field['name']) and is_object($field['name'])) {
                    if ($field['name'] instanceof \DateTime) {
                        $data[$filedName]['name'] = $field['name']->format('Y-m-d');
                    } elseif ($field['name'] instanceof DateRange) {
                        $data[$filedName]['name'] = 'DateRange :: '.$field['name']->serialize();
                    } else {
                        $entity = $field['name'];
                        $getIdentifier = 'getId';
                        if (!method_exists($entity, $getIdentifier)) {
                            $this->entityManager = $this->doctrine->getManager($emName);
                            $identifier = $this->entityManager
                                ->getClassMetadata(get_class($entity))
                                ->getSingleIdentifierFieldName();
                            $getIdentifier = sprintf('get%s', ucfirst($identifier));
                        }
                        $data[$filedName]['name'] = sprintf("CLASS %s %u", get_class($entity), $entity->$getIdentifier());
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
        $this->entityManager = $this->doctrine->getManager($emName);
        $data = $this->session->get($filterName);
        foreach ($data as $filedName => $field) {
            $date = date_parse($field['name']);
            $output = [];
            if (preg_match("/^(CLASS)\s(.*)\s(\d+)$/", $field['name'], $output)) {
                $entity = $this->entityManager->find($output[2], $output[3]);
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
            } elseif ($date and checkdate($date['month'], $date['day'], $date['year'])) {
                $data[$filedName]['name'] = new \DateTime($field['name']);
            }
        }

        return $data;
    }
}
