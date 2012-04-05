<?php

namespace Tusk\RedisMqBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('TuskRedisMqBundle:Default:index.html.twig', array('name' => $name));
    }
}
