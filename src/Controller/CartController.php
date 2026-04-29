<?php
// src/Controller/CartController.php
namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, RedirectResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier', name: 'app_cart_')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cart) {}

    // ── Afficher le panier ────────────────────────────────────────────────────
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal(),
        ]);
    }

    // ── Ajouter un produit ────────────────────────────────────────────────────
    #[Route('/ajouter/{id}', name: 'add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function add(int $id, Request $request, ProduitRepository $repo): RedirectResponse
    {
        $produit = $repo->find($id);
        if (!$produit || $produit->getStockProd() === 0) {
            $this->addFlash('error', 'Ce produit n\'est pas disponible.');
            return $this->redirectToRoute('app_produit_show', ['id' => $id]);
        }

        $quantite = max(1, (int) $request->request->get('quantite', 1));
        $this->cart->add($id, $quantite);

        $this->addFlash('success', sprintf(
            '"%s" ajouté au panier.', $produit->getNomProd()
        ));

        $referer = $request->headers->get('referer');
        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('app_cart_index');
    }

    // ── Mettre à jour la quantité ─────────────────────────────────────────────
    #[Route('/modifier/{id}', name: 'update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $quantite = (int) $request->request->get('quantite', 1);
        $this->cart->setQuantite($id, $quantite);

        if ($quantite <= 0) {
            $this->addFlash('info', 'Article retiré du panier.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    // ── Supprimer un article ──────────────────────────────────────────────────
    #[Route('/supprimer/{id}', name: 'remove', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function remove(int $id): RedirectResponse
    {
        $this->cart->remove($id);
        $this->addFlash('info', 'Article retiré du panier.');
        return $this->redirectToRoute('app_cart_index');
    }

    // ── Vider le panier ───────────────────────────────────────────────────────
    #[Route('/vider', name: 'clear', methods: ['POST'])]
    public function clear(): RedirectResponse
    {
        $this->cart->clear();
        $this->addFlash('info', 'Panier vidé.');
        return $this->redirectToRoute('app_cart_index');
    }

    // ── Compteur AJAX (pour la navbar) ───────────────────────────────────────
    #[Route('/count', name: 'count')]
    public function count(): JsonResponse
    {
        return new JsonResponse(['count' => $this->cart->getCount()]);
    }
}