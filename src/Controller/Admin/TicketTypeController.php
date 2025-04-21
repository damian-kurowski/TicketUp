<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\TicketType;
use App\Form\Admin\TicketTypeType;
use App\Repository\EventRepository;
use App\Repository\TicketTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/admin/event/{event_id}/ticket-type')]
#[IsGranted('ROLE_ADMIN')]
class TicketTypeController extends AbstractController
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    #[Route('/', name: 'app_admin_ticket_type_index', requirements: ['event_id' => '\d+'], methods: ['GET'])]
    public function index(int $event_id, TicketTypeRepository $ticketTypeRepository): Response
    {
        $event = $this->findEventOrThrow($event_id);
        $ticketTypes = $ticketTypeRepository->findBy(['event' => $event]);

        return $this->render('admin/ticket_type/index.html.twig', [
            'ticket_types' => $ticketTypes,
            'event' => $event,
        ]);
    }

    #[Route('/new', name: 'app_admin_ticket_type_new', requirements: ['event_id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(Request $request, int $event_id, EntityManagerInterface $entityManager): Response
    {
        $event = $this->findEventOrThrow($event_id);

        $ticketType = new TicketType();
        $ticketType->setEvent($event);

        $form = $this->createForm(TicketTypeType::class, $ticketType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticketType->setAvailableQuantity($ticketType->getQuantity());
            $entityManager->persist($ticketType);
            $entityManager->flush();
            $this->addFlash('success', 'Nowy typ biletu został dodany.');
            return $this->redirectToRoute('app_admin_ticket_type_index', ['event_id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/ticket_type/new.html.twig', [
            'ticket_type' => $ticketType,
            'form' => $form,
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_ticket_type_show', requirements: ['event_id' => '\d+', 'id' => '\d+'], methods: ['GET'])]
    public function show(int $event_id, TicketType $ticketType): Response
    {
        $event = $this->findEventOrThrow($event_id);
        if ($ticketType->getEvent() !== $event) {
            throw $this->createNotFoundException('Nie znaleziono takiego typu biletu dla tego wydarzenia.');
        }

        return $this->render('admin/ticket_type/show.html.twig', [
            'ticket_type' => $ticketType,
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_ticket_type_edit', requirements: ['event_id' => '\d+', 'id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $event_id, TicketType $ticketType, EntityManagerInterface $entityManager): Response
    {
        $event = $this->findEventOrThrow($event_id);
        if ($ticketType->getEvent() !== $event) {
            throw $this->createNotFoundException('Nie znaleziono takiego typu biletu dla tego wydarzenia.');
        }

        $form = $this->createForm(TicketTypeType::class, $ticketType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Zmiany w typie biletu zostały zapisane.');
            return $this->redirectToRoute('app_admin_ticket_type_index', ['event_id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/ticket_type/edit.html.twig', [
            'ticket_type' => $ticketType,
            'form' => $form,
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_ticket_type_delete', requirements: ['event_id' => '\d+', 'id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $event_id, TicketType $ticketType, EntityManagerInterface $entityManager): Response
    {
        $event = $this->findEventOrThrow($event_id);
        if ($ticketType->getEvent() !== $event) {
            throw $this->createNotFoundException('Nie znaleziono takiego typu biletu dla tego wydarzenia.');
        }

        if ($this->isCsrfTokenValid('delete'.$ticketType->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticketType);
            $entityManager->flush();
            $this->addFlash('info', 'Typ biletu został usunięty.');
        } else {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF.');
        }

        return $this->redirectToRoute('app_admin_ticket_type_index', ['event_id' => $event->getId()], Response::HTTP_SEE_OTHER);
    }

    private function findEventOrThrow(int $event_id): Event
    {
        $event = $this->eventRepository->find($event_id);
        if (!$event) {
            throw $this->createNotFoundException('Nie znaleziono wydarzenia o ID: ' . $event_id);
        }
        return $event;
    }
}