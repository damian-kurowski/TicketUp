<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/order')]
#[IsGranted('ROLE_ADMIN')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_admin_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

}