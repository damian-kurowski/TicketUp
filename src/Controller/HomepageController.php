<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {

        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }
    #[Route('/testmail', name: 'app_test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $testEmail = (new Email())
            ->from('test-nadawca@ticketup.local')
            ->to('test-odbiorca@example.com')
            ->subject('Symfony Mailer Test @ ' . date('Y-m-d H:i:s'))
            ->text('This is a simple test email sent from the test action.');

        $message = '';
        try {
            $mailer->send($testEmail);
            $message = 'OK! Próba wysłania testowego emaila zakończona. Sprawdź Profiler -> Mailer.';
            $this->addFlash('success', $message);
        } catch (\Exception $e) {
            $message = 'BŁĄD podczas próby wysłania testowego emaila: ' . $e->getMessage();
            $this->addFlash('danger', $message);
        }

        return $this->redirectToRoute('app_homepage');
    }
}