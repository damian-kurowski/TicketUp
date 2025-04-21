<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\Admin\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/event')]
#[IsGranted('ROLE_ADMIN')]
final class AdminEventController extends AbstractController
{
    private string $eventImagesDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        #[Autowire('%event_images_directory%')] string $eventImagesDirectory,
        SluggerInterface $slugger
    ) {
        $this->eventImagesDirectory = $eventImagesDirectory;
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'app_admin_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('admin/event/index.html.twig', [
            'events' => $eventRepository->findBy([], ['startDate' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_admin_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setStartDate(new \DateTimeImmutable('+1 day'));
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->eventImagesDirectory, $newFilename);
                    $event->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Nie udało się przesłać obrazka: ' . $e->getMessage());

                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Wydarzenie zostało pomyślnie dodane.');
            return $this->redirectToRoute('app_admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_event_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->eventImagesDirectory, $newFilename);

                    $event->setImageFilename($newFilename);

                } catch (FileException $e) {
                    $this->addFlash('danger', 'Nie udało się przesłać nowego obrazka: ' . $e->getMessage());
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Zmiany w wydarzeniu zostały zapisane.');
            return $this->redirectToRoute('app_admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_event_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $imageFilename = $event->getImageFilename();

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('info', 'Wydarzenie zostało usunięte.');
        } else {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF dla operacji usuwania.');
        }


        return $this->redirectToRoute('app_admin_event_index', [], Response::HTTP_SEE_OTHER);
    }
}