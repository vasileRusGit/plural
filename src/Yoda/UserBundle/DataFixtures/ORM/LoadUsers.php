<?php

namespace Yoda\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Yoda\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadUsers implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface {

    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $user = new user();
        $user->setUsername('user');
        $user->setEmail('user@mail.com');
        $user->setPassword($this->encodePassword($user, 'user'));
        $manager->persist($user);
        
        $admin = new user();
        $admin->setUsername('admin');
        $admin->setEmail('admin@mail.com');
        $admin->setPassword($this->encodePassword($admin, 'admin'));
        $admin->setRoles(array('ROLE_ADMIN'));
//        $admin->setIsActive(false);
        $manager->persist($admin);

        $manager->flush();
    }
    
    private function encodePassword(User $user, $plainPassword){
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

        return $encoder->encodePassword($plainPassword, $user->getSalt());
    }


    public function setContainer(ContainerInterface $container = null){
        $this->container = $container;
    }
    
    public function getOrder() {
        return 10;
    }

}
