<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\EventRepository;
use App\Entity\Event as UserEvent;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private $eventRepository;
    private $router;
    /** @var User $user **/
    private $user;

    public function __construct(
        EventRepository $eventRepository,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventRepository = $eventRepository;
        $this->router = $router;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $dateFormat = 'Y-m-d H:i:s';
        $start = $calendar->getStart()->format($dateFormat);
        $end = $calendar->getEnd()->format($dateFormat);
        $filters = $calendar->getFilters();
        $userEvents = $this->getAllowedEvents($start, $end);

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

    private function getAllowedEvents($start, $end)
    {
        if ($this->hasUserRole('ROLE_APP_ADMIN')) {
            $allowedEvents = $this->getAllEvents($start, $end);
        } elseif ($this->hasUserRole('ROLE_GROUP_ADMIN')) {
            $allowedEvents = $this->getGroupEvents($start, $end);
        } else {
            $allowedEvents = $this->getUserEvents($start, $end);
        }

        return $allowedEvents;
    }

    private function hasUserRole($role)
    {
        $roles = $this->user ? $this->user->getRoles() : [];
        $hasRole = false;

        foreach ($roles as $userRole) {
            if ($userRole === $role) {
                $hasRole = true;
                break;
            }
        }

        return $hasRole;
    }

    private function getAllEvents($start, $end)
    {
        return $this->eventRepository
            ->createQueryBuilder('e')
            ->where('e.starts_at BETWEEN :start and :end OR e.ends_at BETWEEN :start and :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getGroupEvents($start, $end)
    {
        $groupsId = $this->user->getGroupsId();

        return $this->eventRepository
            ->createQueryBuilder('e')
            ->where('e.starts_at BETWEEN :start and :end OR e.ends_at BETWEEN :start and :end')
            ->andWhere('g.id IN(:groups_id)')
            ->leftJoin('e.users', 'u')
            ->leftJoin('u.groups', 'g')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('groups_id', $groupsId)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getUserEvents($start, $end)
    {
        $userId = $this->user ? $this->user->getId() : 0;

        return $this->eventRepository
            ->createQueryBuilder('e')
            ->where('e.starts_at BETWEEN :start and :end OR e.ends_at BETWEEN :start and :end')
            ->andWhere('u.id = :user_id')
            ->leftJoin('e.users', 'u')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult()
        ;
    }
}