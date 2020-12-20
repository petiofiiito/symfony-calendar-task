<?php

namespace App\EventSubscriber;

use App\Repository\EventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private $eventRepository;
    private $router;

    public function __construct(
        EventRepository $eventRepository,
        UrlGeneratorInterface $router
    ) {
        $this->eventRepository = $eventRepository;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        $events = $this->eventRepository
            ->createQueryBuilder('event')
            ->where('event.start BETWEEN :start and :end OR event.end BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($events as $event) {
            // this creates the event prefilled with the data
            $eventEvent = new Event(
                $event->getTitle(),
                $event->getStart(),
                $event->getEnd() // If the end date is null or not defined, a all day event is created.
            );

            $eventEvent->setOptions([
                                          'backgroundColor' => 'red',
                                          'borderColor' => 'red',
                                      ]);

            $eventEvent->addOption(
                'url',
                $this->router->generate('event_show', [
                    'id' => $event->getId(),
                ])
            );

            $calendar->addEvent($eventEvent);
        }
    }
}