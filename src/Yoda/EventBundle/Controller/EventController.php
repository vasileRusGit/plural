<?php

namespace Yoda\EventBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Yoda\EventBundle\Entity\Event;
use Yoda\EventBundle\Form\EventType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Event controller.
 *
 */
class EventController extends Controller {

    /**
     * Lists all Event entities.
     *
     */
    public function indexAction() {
        // testing the error page
//        throw new \Exception('error');
        
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('EventBundle:Event')->getUpcomingEvents();

        return $this->render('event/index.html.twig', array(
                    'events' => $events,
        ));
//        return array();
    }
    
    public function upcomingEventsAction($max = null)
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('EventBundle:Event')->getUpcomingEvents($max);
        
        return $this->render('event/upcomingEvents.html.twig', array('events' => $events));
    }

    /**
     * Creates a new Event entity.
     *
     */
    public function newAction(Request $request) {
        $this->enforceUserSecurity();
        $event = new Event();
        $form = $this->createForm(new EventType(), $event);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $event->setOwner($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('event_show', array('slug' => $event->getSlug()));
        }

        return $this->render('event/new.html.twig', array(
                    'event' => $event,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Event entity.
     *
     */
    public function showAction($slug) {
        $this->enforceUserSecurity();

        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->findOneBy(array('slug' => $slug));

        $deleteForm = $this->createDeleteForm($event);

        return $this->render('event/show.html.twig', array(
                    'event' => $event,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Event entity.
     *
     */
    public function editAction(Request $request, Event $event) {
        $this->enforceUserSecurity();

        $deleteForm = $this->createDeleteForm($event);
        $updateForm = $this->createUpdateForm($event);
        $editForm = $this->createForm(new EventType(), $event);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('event_show', array('slug' => $event->getSlug()));
        }

        $this->enforceOwnerSecurity($event);

        return $this->render('event/edit.html.twig', array(
                    'event' => $event,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'update_form' => $updateForm->createView(),
        ));
    }

    /**
     * Deletes a Event entity.
     *
     */
    public function deleteAction(Request $request, Event $event) {
        $this->enforceAdminUserSecurity();

        $form = $this->createDeleteForm($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($event);
            $em->flush();
        }

        $this->enforceOwnerSecurity($event);

        return $this->redirectToRoute('event_index');
    }

    public function attendAction($id, $format) {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getrepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw new Exception("Unable to find Event.");
        }
        
        //condition to not add duplicate users
        if (!$event->hasAttendee($this->getUser())) {
            $event->getAttendees()->add($this->getUser());
        }
        $em->persist($event);
        $em->flush();
        
        return $this->createAttendingResponse($event, $format);
    }

    public function unattendAction($id, $format) {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getrepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw new Exception("Unable to find Event.");
        }
        
        //condition to not add duplicate users
        if ($event->hasAttendee($this->getUser())) {
            $event->getAttendees()->removeElement($this->getUser());
        }
        $em->persist($event);
        $em->flush();
        
        return $this->createAttendingResponse($event, $format);
    }
    
    public function createAttendingResponse(Event $event, $format)
    {
        // return a json respons | a controller always return a response object
        if($format == 'json'){
            $data = array('attending' => $event->hasAttendee($this->getUser()));
            
            $response = new JsonResponse($data);

            return $response;
        }

        $url = $this->generateUrl('event_show', array('slug' => $event->getSlug()));

        return $this->redirect($url);
    }

    /**
     * Creates a form to delete a Event entity.
     *
     * @param Event $event The Event entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createUpdateForm(Event $event) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('event_edit', array('id' => $event->getId())))
                        ->add('submit', 'submit', array('label' => 'Update'))
                        ->setMethod('POST')
                        ->getForm()
        ;
    }

    /**
     * Creates a form to delete a Event entity.
     *
     * @param Event $event The Event entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Event $event) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('event_delete', array('id' => $event->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    public function enforceAdminUserSecurity() {
        if (!$this->getSecurityContext()->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Need ROLE_ADMIN!');
        }
    }

    public function enforceUserSecurity() {
        if (!$this->getSecurityContext()->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Need ROLE_USER!');
        }
    }

}
