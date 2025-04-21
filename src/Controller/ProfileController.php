<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Musisz być zalogowany, aby zobaczyć tę stronę.');
        }

        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}