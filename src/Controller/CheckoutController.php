<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Form\OrderType;
use App\Message\ProcessPaidOrder;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CheckoutController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/checkout', name: 'app_checkout_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CartService $cartService, EntityManagerInterface $em): Response
    {
        $cartItems = $cartService->getCartDetails();
        $total = $cartService->getTotal();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Twój koszyk jest pusty, nie możesz przejść do kasy.');
            return $this->redirectToRoute('app_cart_show');
        }

        $order = new Order();
        $user = $this->getUser();
        if ($user instanceof User) {
            $order->setUser($user);
            $order->setCustomerEmail($user->getEmail() ?? '');
            $order->setCustomerFirstName($user->getFirstName() ?? '');
            $order->setCustomerLastName($user->getLastName() ?? '');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->beginTransaction();
            try {
                $order->setStatus('new');
                $order->setTotalAmount($total);
                $em->persist($order);

                foreach ($cartItems as $item) {
                    $ticketType = $item['ticketType'];
                    $quantity = $item['quantity'];
                    $em->refresh($ticketType);

                    if ($ticketType->getAvailableQuantity() < $quantity) {
                        throw new \RuntimeException("Niewystarczająca ilość biletów typu '{$ticketType->getName()}'. Dostępne: {$ticketType->getAvailableQuantity()}.");
                    }
                    $ticketType->setAvailableQuantity($ticketType->getAvailableQuantity() - $quantity);
                    $em->persist($ticketType);

                    $orderItem = new OrderItem();
                    $orderItem->setRelatedOrder($order);
                    $orderItem->setTicketType($ticketType);
                    $orderItem->setQuantity($quantity);
                    $orderItem->setUnitPrice($ticketType->getPrice());
                    $orderItem->setTotalPrice($item['subtotal']);
                    $em->persist($orderItem);
                }

                $em->flush();
                $em->commit();

                $cartService->clear();
                $this->addFlash('info', 'Zamówienie zostało przyjęte (#' . $order->getId() . '). Sprawdź dane i zasymuluj płatność.');

                return $this->redirectToRoute('app_checkout_summary', ['id' => $order->getId()]);

            } catch (\Exception $e) {
                $em->rollback();
                $this->logger->error('Checkout Error: ' . $e->getMessage(), ['exception' => $e]);
                $this->addFlash('danger', 'Wystąpił nieoczekiwany błąd podczas składania zamówienia. Spróbuj ponownie lub skontaktuj się z pomocą.');
                return $this->redirectToRoute('app_cart_show');
            }
        }

        return $this->render('checkout/index.html.twig', [
            'orderForm' => $form,
            'cartItems' => $cartItems,
            'total' => $total,
            'cartItemCount' => $cartService->getCartItemCount()
        ]);
    }



    #[Route('/checkout/summary/{id}', name: 'app_checkout_summary', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function summary(Order $order): Response
    {
        if ($order->getStatus() !== 'new') {
            $this->addFlash('info', 'To zamówienie zostało już przetworzone lub anulowane.');
            return $this->redirectToRoute('app_homepage');
        }

        $user = $this->getUser();
        if ($order->getUser() && $order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tego zamówienia.');
        }

        return $this->render('checkout/summary.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/checkout/simulate-payment/{id}', name: 'app_checkout_simulate_payment', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function simulatePayment(Request $request, Order $order, EntityManagerInterface $em, MessageBusInterface $messageBus, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('simulate_payment'.$order->getId(), $token)) {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF. Spróbuj ponownie.');
            return $this->redirectToRoute('app_checkout_summary', ['id' => $order->getId()]);
        }

        if ($order->getStatus() !== 'new') {
            $this->addFlash('info', 'Płatność za to zamówienie została już przetworzona lub zamówienie anulowano.');
            if ($order->getStatus() === 'paid') {
                return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
            }
            return $this->redirectToRoute('app_homepage');
        }

        $user = $this->getUser();
        if ($order->getUser() && $order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $order->setStatus('paid');
        $em->persist($order);
        $em->flush();

        if (!class_exists(ProcessPaidOrder::class)) {
            $this->addFlash('warning', 'Klasa wiadomości ProcessPaidOrder nie istnieje - pominięto generowanie biletów/emaila.');
            $this->logger->warning('ProcessPaidOrder message class not found for order #' . $order->getId());
        } else {
            try {
                $messageBus->dispatch(new ProcessPaidOrder($order->getId()));
                $this->addFlash('success', 'Płatność została pomyślnie zasymulowana! Twoje zamówienie jest przetwarzane.');
            } catch (MessengerExceptionInterface $e) {
                $this->logger->error('Failed to dispatch ProcessPaidOrder message for order #' . $order->getId(), ['exception' => $e]);
                $this->addFlash('danger', 'Wystąpił błąd podczas inicjowania generowania biletów. Skontaktuj się z obsługą.');
            }
        }

        return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
    }

    #[Route('/checkout/success/{id}', name: 'app_checkout_success', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function success(Order $order): Response
    {
        // Dostęp do tej strony powinien być możliwy tylko po opłaceniu
        if ($order->getStatus() !== 'paid') {
            $this->addFlash('warning', 'To zamówienie nie zostało jeszcze opłacone.');
            if ($order->getStatus() === 'new') {
                return $this->redirectToRoute('app_checkout_summary', ['id' => $order->getId()]);
            }
            return $this->redirectToRoute('app_homepage');
        }

        $user = $this->getUser();
        if ($order->getUser() && $order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }
}