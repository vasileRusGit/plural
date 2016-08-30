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

use Yoda\EventBundle\Entity\Event;

$event = new Event();
$event->setName('Popescu');
$event->setLocation("New York");
$event->setTime(new DateTime('tomorrow noon'));
$event->setDetails('Ha ha ha...');

$em = $container->get('doctrine')->getManager();
$em->persist($event);
$em->flush();