<?php

namespace App\Service;

use App\Entity\TicketType;
use App\Repository\TicketTypeRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartService
{
    private RequestStack $requestStack;
    private TicketTypeRepository $ticketTypeRepository;
    private const CART_SESSION_KEY = 'ticketup_cart';

    public function __construct(RequestStack $requestStack, TicketTypeRepository $ticketTypeRepository)
    {
        $this->requestStack = $requestStack;
        $this->ticketTypeRepository = $ticketTypeRepository;
    }

    private function getSession(): SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (BadRequestHttpException $e) {

            throw new \LogicException('Cannot get session outside of HTTP request context.', 0, $e);
        }
    }

    public function add(int $ticketTypeId, int $quantity): void
    {
        if ($quantity <= 0) return;

        $ticketType = $this->ticketTypeRepository->find($ticketTypeId);
        if (!$ticketType) return;

        $session = $this->getSession();
        $cart = $this->getCartFromSession($session);
        $currentQuantityInCart = $cart[$ticketTypeId] ?? 0;
        $newQuantity = $currentQuantityInCart + $quantity;

        if ($newQuantity > $ticketType->getAvailableQuantity()) {
            $newQuantity = $ticketType->getAvailableQuantity();
            if ($newQuantity <= $currentQuantityInCart) return;
        }

        $cart[$ticketTypeId] = $newQuantity;
        $session->set(self::CART_SESSION_KEY, $cart);
    }

    public function remove(int $ticketTypeId): void
    {
        $session = $this->getSession();
        $cart = $this->getCartFromSession($session);
        unset($cart[$ticketTypeId]);
        $session->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Zmniejsza ilość danego typu biletu o 1. Jeśli ilość spadnie do 0, usuwa pozycję.
     * @return bool True jeśli pozycja została usunięta, False jeśli tylko zmniejszono ilość.
     */
    public function decrease(int $ticketTypeId): bool
    {
        $session = $this->getSession();
        $cart = $this->getCartFromSession($session);
        $removed = false;

        if (isset($cart[$ticketTypeId])) {
            $cart[$ticketTypeId]--;
            if ($cart[$ticketTypeId] <= 0) {
                unset($cart[$ticketTypeId]);
                $removed = true;
            }
        }
        $session->set(self::CART_SESSION_KEY, $cart);
        return $removed;
    }

    public function clear(): void
    {
        $session = $this->getSession();
        $session->remove(self::CART_SESSION_KEY);
    }

    private function getCartFromSession(SessionInterface $session): array
    {
        return $session->get(self::CART_SESSION_KEY, []);
    }

    public function getCart(): array
    {
        return $this->getCartFromSession($this->getSession());
    }


    public function getCartDetails(): array
    {
        $session = $this->getSession();
        $cart = $this->getCartFromSession($session);
        $detailedCart = [];

        foreach ($cart as $id => $quantity) {
            $ticketType = $this->ticketTypeRepository->find($id);
            if ($ticketType && $quantity > 0) {
                if ($quantity > $ticketType->getAvailableQuantity()) {
                    $quantity = $ticketType->getAvailableQuantity();
                    $cart[$id] = $quantity;
                    $session->set(self::CART_SESSION_KEY, $cart);
                    if ($quantity <= 0) {
                        $this->remove($id);
                        continue;
                    }
                }
                $detailedCart[] = [
                    'ticketType' => $ticketType,
                    'quantity' => $quantity,
                    'subtotal' => $ticketType->getPrice() * $quantity,
                ];
            } else {
                $this->remove($id);
            }
        }
        return $detailedCart;
    }

    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->getCartDetails() as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    public function getCartItemCount(): int
    {
        $count = 0;
        foreach ($this->getCart() as $quantity) {
            $count += $quantity;
        }
        return $count;
    }
}