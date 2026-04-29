<?php
// src/Service/CartService.php
namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const SESSION_KEY = 'panier';

    public function __construct(
        private RequestStack      $requestStack,
        private ProduitRepository $produitRepository,
    ) {}

    private function getSession(): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return $this->requestStack->getSession();
    }

    // ── Ajouter un produit (ou incrémenter la quantité) ──────────────────────
    public function add(int $produitId, int $quantite = 1): void
    {
        $panier = $this->getRaw();
        $panier[$produitId] = ($panier[$produitId] ?? 0) + $quantite;
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    // ── Modifier la quantité directement ─────────────────────────────────────
    public function setQuantite(int $produitId, int $quantite): void
    {
        $panier = $this->getRaw();
        if ($quantite <= 0) {
            unset($panier[$produitId]);
        } else {
            $panier[$produitId] = $quantite;
        }
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    // ── Supprimer un produit ──────────────────────────────────────────────────
    public function remove(int $produitId): void
    {
        $panier = $this->getRaw();
        unset($panier[$produitId]);
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    // ── Vider le panier ───────────────────────────────────────────────────────
    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
    }

    // ── Panier brut (tableau id => quantité) ─────────────────────────────────
    public function getRaw(): array
    {
        return $this->getSession()->get(self::SESSION_KEY, []);
    }

    // ── Panier enrichi avec les entités Produit ───────────────────────────────
    public function getItems(): array
    {
        $panier = $this->getRaw();
        $items  = [];

        foreach ($panier as $id => $quantite) {
            $produit = $this->produitRepository->find($id);
            if (!$produit) {
                continue; // produit supprimé entre-temps
            }
            // Limiter la quantité au stock disponible
            $qte = min($quantite, $produit->getStockProd());
            if ($qte <= 0) {
                continue;
            }
            $items[] = [
                'produit'   => $produit,
                'quantite'  => $qte,
                'sousTotal' => bcmul($produit->getPrixProd(), (string) $qte, 2),
            ];
        }

        return $items;
    }

    // ── Nombre total d'articles dans le panier ────────────────────────────────
    public function getCount(): int
    {
        return array_sum($this->getRaw());
    }

    // ── Montant total HT ──────────────────────────────────────────────────────
    public function getTotal(): string
    {
        $total = '0.00';
        foreach ($this->getItems() as $item) {
            $total = bcadd($total, $item['sousTotal'], 2);
        }
        return $total;
    }

    // ── Vérifier que le panier n'est pas vide ────────────────────────────────
    public function isEmpty(): bool
    {
        return empty($this->getRaw());
    }

    // ── Vérifier la disponibilité de tous les articles ───────────────────────
    public function checkStock(): array
    {
        $erreurs = [];
        foreach ($this->getRaw() as $id => $quantite) {
            $produit = $this->produitRepository->find($id);
            if (!$produit) {
                $erreurs[] = "Le produit #$id n'existe plus.";
                continue;
            }
            if ($produit->getStockProd() < $quantite) {
                $erreurs[] = sprintf(
                    '"%s" : stock insuffisant (%d disponibles, %d demandés).',
                    $produit->getNomProd(),
                    $produit->getStockProd(),
                    $quantite
                );
            }
        }
        return $erreurs;
    }
}