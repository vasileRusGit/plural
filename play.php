<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;


/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__.'/app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->boot();

$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request);

//////////////////////////////////////////

use Doctrine\ORM\EntityManager;

/**
 * @var EntityManager $em
 */
$em = $container->get('doctrine')->getManager();

$user = $em->getRepository('UserBundle:User')->findOneByUsernameOrEmail('user');

foreach($user->getEvents() as $event) {
    var_dump($event->getName());
}