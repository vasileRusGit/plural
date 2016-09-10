<?php

namespace Yoda\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Yoda\EventBundle\Entity\Event;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Yoda\UserBundle\Entity\User;

class LoadEvent implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $admin = $manager->getRepository('UserBundle:User')->findOneByUsernameOrEmail('admin');

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

        // assign the 2 events to 'admin' user
        $event1->setOwner($admin);
        $event2->setOwner($admin);

        $manager->flush();
    }

    // order to execute the load users and load events
    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 20;
    }
}
