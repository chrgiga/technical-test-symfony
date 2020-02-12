<?php

namespace App\EventSubscriber;

use App\Repository\EventRepository;
use App\Entity\Event as UserEvent;
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

        $userEvents = $this->eventRepository
            ->createQueryBuilder('event')
            ->where('event.starts_at BETWEEN :start and :end OR event.ends_at BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        /**
         * @var UserEvent $userEvent
         */
        foreach ($userEvents as $userEvent) {
            $bookingEvent = new Event(
                $userEvent->getName(),
                $userEvent->getStartsAt(),
                $userEvent->getEndsAt()
            );

            $bookingEvent->setOptions([
                'backgroundColor' => 'red',
                'borderColor' => 'red',
            ]);
            $bookingEvent->addOption(
                'url',
                $this->router->generate('event', [
                    'eventId' => $userEvent->getId(),
                ])
            );

            $calendar->addEvent($bookingEvent);
        }
    }
}