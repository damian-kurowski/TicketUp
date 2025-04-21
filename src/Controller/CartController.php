<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment as TwigEnvironment;

#[Route('/cart')]
class CartController extends AbstractController
{
    private TwigEnvironment $twig;

    public function __construct(
        TwigEnvironment $twig
    ) {
        $this->twig = $twig;
    }


    #[Route('/add', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, CartService $cartService, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $csrfId = 'add_to_cart_token';
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid($csrfId, $submittedToken)) {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF. Spróbuj ponownie.');
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                return $this->renderTurboStream('partials/_flash_messages.html.twig');
            }
            $referer = $request->headers->get('referer');
            return $this->redirect($referer ?: $this->generateUrl('app_homepage'));
        }

        $quantities = $request->request->all('quantity');
        $addedItemsCount = 0;

        foreach ($quantities as $ticketTypeId => $quantity) {
            $ticketTypeId = (int)$ticketTypeId;
            $quantity = (int)$quantity;

            if ($ticketTypeId > 0 && $quantity > 0) {
                $cartService->add($ticketTypeId, $quantity);
                $addedItemsCount += $quantity;
            }
        }

        if ($addedItemsCount > 0) {
            $this->addFlash('success', sprintf('Dodano %d bilet(ów) do koszyka!', $addedItemsCount));
        } else {
            $this->addFlash('warning', 'Nie wybrano ilości biletów do dodania.');
        }

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            return $this->renderTurboStream('cart/add.stream.html.twig', [
                'cartItemCount' => $cartService->getCartItemCount()
            ]);
        }

        return $this->redirectToRoute('app_cart_show');
    }


    #[Route('/', name: 'app_cart_show', methods: ['GET'])]
    public function show(CartService $cartService): Response
    {
        $detailedCart = $cartService->getCartDetails();
        $total = $cartService->getTotal();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $detailedCart,
            'total' => $total,
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(int $id, CartService $cartService, Request $request): Response
    {
        $submittedToken = $request->request->get('_token');
        $csrfId = 'remove_item'.$id;

        if (!$this->isCsrfTokenValid($csrfId, $submittedToken)) {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF.');
        } else {
            $cartService->remove($id);
            $this->addFlash('info', 'Usunięto pozycję z koszyka.');
        }

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            return $this->renderTurboStream('cart/remove.stream.html.twig', [
                'id_to_remove' => $id,
                'cartItemCount' => $cartService->getCartItemCount(),
                'total' => $cartService->getTotal(),
            ]);
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function decrease(int $id, CartService $cartService, Request $request): Response
    {
        $submittedToken = $request->request->get('_token');
        $csrfId = 'decrease_item'.$id;
        $itemRemoved = false;

        if (!$this->isCsrfTokenValid($csrfId, $submittedToken)) {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF.');
        } else {
            $itemRemoved = $cartService->decrease($id);
            $this->addFlash('info', $itemRemoved ? 'Usunięto ostatnią sztukę z koszyka.' : 'Zmniejszono ilość w koszyku.');
        }

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $cartItemCount = $cartService->getCartItemCount();
            $total = $cartService->getTotal();

            if ($itemRemoved) {
                return $this->renderTurboStream('cart/remove.stream.html.twig', [
                    'id_to_remove' => $id,
                    'cartItemCount' => $cartItemCount,
                    'total' => $total,
                ]);
            } else {
                $updatedItem = null;
                foreach ($cartService->getCartDetails() as $item) {
                    if ($item['ticketType']->getId() === $id) {
                        $updatedItem = $item;
                        break;
                    }
                }
                return $this->renderTurboStream('cart/update_item.stream.html.twig', [
                    'item' => $updatedItem,
                    'cartItemCount' => $cartItemCount,
                    'total' => $total,
                ]);
            }
        }

        return $this->redirectToRoute('app_cart_show');
    }

    private function renderTurboStream(string $templatePath, array $context = []): Response
    {
        $content = $this->twig->render($templatePath, $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            ['Content-Type' => TurboBundle::STREAM_FORMAT]
        );

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }
}