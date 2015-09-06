<?php

namespace Zk2\SPSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('Zk2SPSBundle:Default:index.html.twig', array('name' => $name));
    }
}
