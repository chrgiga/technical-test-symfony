<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/events")
 */
class EventController extends AbstractController
{
    protected $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/", name="events")
     */
    public function index(): Response
    {
        return $this->render('events/events.html.twig');
    }

    /**
     * @Route("/{eventId}", name="event")
     */
    public function event($eventId): Response
    {
        $event = $this->eventRepository->find($eventId);

        return $this->render('events/event.html.twig', ['event' => $event]);
    }
}
