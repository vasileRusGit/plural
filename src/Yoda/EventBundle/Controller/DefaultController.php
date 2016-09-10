<?php

namespace Yoda\EventBundle\Controller;


class DefaultController extends Controller
{
    public function indexAction($name)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('EventBundle:Event');
        
        $event = $repo->findOneBy(array('name' => 'Popescu'));
        
        return $this->render('EventBundle:Default:index.html.twig', array('name' => $name, "event" => $event));
    }
}
