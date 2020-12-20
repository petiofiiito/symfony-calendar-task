<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/list", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(Request $request, ValidatorInterface $validator, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $start = $request->request->get('start-date');
        if (!empty($start)) {
            $start = new \DateTime($start);
            $event->setStart($start);
        }
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);

            $errors = $validator->validate($event);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } elseif ($eventRepository->findByStart($event->getStart())) {
                return new Response('Cannot have more than 1 event at the same time!', 400);
            } elseif (
                $eventRepository
                ->createQueryBuilder('event')
                ->where('DATE_FORMAT(event.end,\'%Y-%m-%d\') > :start AND event.end > :start')
                ->setParameter('start', $event->getStart()->format('Y-m-d'))
                ->getQuery()
                ->getResult()
            ) {
                return new Response('Cannot have the event start before the previous one ends!', 400);
            }

            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventRepository->findByStart($event->getStart())) {
                foreach ($eventRepository->findByStart($event->getStart()) as $record) {
                    if ($event->getId() !== $record->getId()) {
                        return new Response('Cannot have more than 1 event at the same time!', 400);
                    }
                }
            } elseif (
                $eventRepository
                ->createQueryBuilder('event')
                ->where('DATE_FORMAT(event.end,\'%Y-%m-%d\') > :start AND event.end > :start')
                ->setParameter('start', $event->getStart()->format('Y-m-d'))
                ->getQuery()
                ->getResult()
            ) {
                foreach ($eventRepository->findByStart($event->getStart()) as $record) {
                    if ($event->getId() !== $record->getId()) {
                        return new Response('Cannot have the event start before the previous one ends!', 400);
                    }
                }
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/", name="event_calendar", methods={"GET"})
     */
    public function calendar(): Response
    {
        return $this->render('event/calendar.html.twig');
    }
}
