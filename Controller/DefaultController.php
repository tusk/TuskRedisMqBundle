<?php

namespace Tusk\RedisQueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('TuskRedisQueueBundle:Default:index.html.twig', array('name' => $name));
    }
}
