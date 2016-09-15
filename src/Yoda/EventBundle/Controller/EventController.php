<?php

namespace Yoda\EventBundle\Controller;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Yoda\EventBundle\Entity\Event;
use Yoda\EventBundle\Form\EventType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Event controller.
 *
 */
class EventController extends Controller
{
    /**
     * Lists all Event entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('EventBundle:Event')->getUpcomingEvents();

        return $this->render('event/index.html.twig', array(
            'events' => $events,
        ));
    }


    /**
     * Creates a new Event event.
     *
     */
    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();
        $event = new Event();
        $form = $this->createForm(new EventType(), $event);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // owner id
            $user = $this->getUser();
            $event->setOwner($user);

//            var_dump($event);die;

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

    public function enforceUserSecurity()
    {
        if (!$this->getSecurityContext()->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Need ROLE_USER!');
        }
    }

    /**
     * Finds and displays a Event entity.
     *
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->findOneBy(array('slug' => $slug));

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $deleteForm = $this->createDeleteForm($event->getId());

        return $this->render('event/show.html.twig', array(
            'event' => $event,
            'delete_form' => $deleteForm->createView(),));
    }

    /**
     * Creates a form to delete a Event event by id.
     *
     * @param mixed $id The event id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('event_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Displays a form to edit an existing Event event.
     *
     */
    public function editAction($id)
    {
        $this->enforceUserSecurity();
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event event.');
        }

        $editForm = $this->createEditForm($event);
        $deleteForm = $this->createDeleteForm($id);

        $this->enforceOwnerSecurity($event);

        return $this->render('event/edit.html.twig', array(
            'event' => $event,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Event event.
     *
     * @param Event $event The event
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Event $event)
    {
        $form = $this->createForm(new EventType(), $event, array(
            'action' => $this->generateUrl('event_update', array('id' => $event->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * Edits an existing Event event.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $this->enforceUserSecurity();
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $this->enforceOwnerSecurity($event);

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($event);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('event_edit', array('id' => $id)));
        }

        return $this->render('EventBundle:Event:edit.html.twig', array(
            'event' => $event,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    // attend and unattend

    /**
     * Deletes a Event event.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $this->enforceUserSecurity();
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $event = $em->getRepository('EventBundle:Event')->find($id);

            if (!$event) {
                throw $this->createNotFoundException('Unable to find Event event.');
            }

            $em->remove($event);
            $em->flush();
        }

        $this->enforceOwnerSecurity($event);

        return $this->redirect($this->generateUrl('event_index'));
    }

    public function attendAction($id, $format)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw new Exception("Your event was not foind!");
        }

        if (!$event->hasAttendee($this->getUser())) {
            $event->getAttendees()->add($this->getUser());
        }

        $em->persist($event);
        $em->flush();

        return $this->createAttendingRespone($event, $format);
    }

    public function unattendAction($id, $format)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw new Exception("Your event was not foind!");
        }

        if ($event->hasAttendee($this->getUser())) {
            $event->getAttendees()->removeElement($this->getUser());
        }

        $em->persist($event);
        $em->flush();

        return $this->createAttendingRespone($event, $format);
    }

    public function enforceAdminUserSecurity()
    {
        if (!$this->getSecurityContext()->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Need ROLE_ADMIN!');
        }
    }

    private function createAttendingRespone(Event $event, $format)
    {
        if ($format === "json") {
            $data = array('attending' => $event->hasAttendee($this->getUser()));

            $response = new JsonResponse($data);

            return $response;
        }

        $url = $this->generateUrl('event_show', array('slug' => $event->getSlug()));

        return $this->redirect($url);
    }


}