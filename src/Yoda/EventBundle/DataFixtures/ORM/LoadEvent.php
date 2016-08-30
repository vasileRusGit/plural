<?php

namespace Yoda\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Yoda\EventBundle\Entity\Event;

class LoadEvent implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $event1 = new Event();
        $event1->setName('Vasi');
        $event1->setLocation('Los Angeles');
        $event1->setTime(new \DateTime('tomorrow noon'));
        $event1->setDetails('Welcome to Los Angeles');
        $manager->persist($event1);
        
        $event2 = new Event();
        $event2->setName('Vlad');
        $event2->setLocation('Halmeu');
        $event2->setTime(new \DateTime('tomorrow noon'));
        $event2->setDetails('Welcome to Los Halmeu');
        $manager->persist($event2);
        
        $manager->flush();
    }
}
