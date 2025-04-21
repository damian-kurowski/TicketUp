<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EventSearchType;
use Symfony\Component\HttpFoundation\Request;

class PublicEventController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    #[Route('/events', name: 'app_event_index')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $searchForm = $this->createForm(EventSearchType::class);
        $searchForm->handleRequest($request);

        $searchQuery = null;
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchQuery = $searchForm->get('query')->getData();
        }

        $allUpcomingEvents = $eventRepository->findUpcomingByName($searchQuery);

        $featuredEvents = array_slice($allUpcomingEvents, 0, 5);

        return $this->render('public_event/index.html.twig', [
            'events' => $allUpcomingEvents,
            'searchForm' => $searchForm,
            'featuredEvents' => $featuredEvents,
            'searchQuery' => $searchQuery
        ]);
    }


    #[Route('/event/{id}', name: 'app_event_show', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        $availableTicketTypes = $event->getTicketTypes()->filter(function($tt) {
            /** @var \App\Entity\TicketType $tt */
            return $tt->getAvailableQuantity() > 0;
        });

        return $this->render('public_event/show.html.twig', [
            'event' => $event,
            'availableTicketTypes' => $availableTicketTypes
        ]);
    }
}