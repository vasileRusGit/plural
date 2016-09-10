<?php

namespace Yoda\UserBundle\Controller;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Yoda\UserBundle\Entity\User;
use Yoda\UserBundle\Form\RegisterFormType;
use Yoda\EventBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class RegisterController extends Controller
{

    /**
     * @Route("/register", name="user_register")
     *
     */
    public function registerAction(Request $request)
    {

        $user = new User();
//        $user->setUsername('Leia');

        $form = $this->createForm(new RegisterFormType(), $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $user->setPassword($this->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // flush messages
            $request->getSession()->getFlashbag()->add('succes', 'Welcome to the website.');


            $url = $this->generateUrl('login');

            return $this->redirect($url);
        }

        return $this->render('register/register.html.twig', array('form' => $form->createView()));
    }

    private function encodePassword(User $user, $plainPassword)
    {
        $encoder = $this->getSecurityContext() ->getEncoder($user);

        return $encoder->encodePassword($plainPassword, $user->getSalt());
    }

    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; //firewall name
        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());

        $this->getSecurityContext()->setToken($token);

    }

}
