<?php

namespace Yoda\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Yoda\EventBundle\Entity\Event;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadEvent implements FixtureInterface, OrderedFixtureInterface {

    public function load(ObjectManager $manager) {
        $admin = $manager->getRepository('UserBundle:User')->findOneByUsernameOrEmail('admin');
        $user  = $manager->getRepository('UserBundle:user')->findOneByUserNameOrEmail('user');

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

        $event1->setOwner($admin);
        $event2->setOwner($user);

        $manager->flush();
    }

    public function getOrder() {
        return 20;
    }

}
