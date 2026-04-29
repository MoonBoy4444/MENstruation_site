<?php
// src/Controller/CheckoutController.php
namespace App\Controller;

use App\Entity\{Adresse, Commande, LigneCommande};
use App\Form\{AdresseLivraisonType, LivraisonChoixType, PaiementChoixType};
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, Session\SessionInterface};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/commande', name: 'app_checkout_')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService            $cart,
        private EntityManagerInterface $em,
    ) {}

    // ── ÉTAPE 1 : Adresse de livraison ───────────────────────────────────────
    #[Route('/adresse', name: 'adresse')]
    public function adresse(Request $request): Response
    {
        if ($this->cart->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        // Vérifier les stocks avant de lancer le tunnel
        $erreurs = $this->cart->checkStock();
        if ($erreurs) {
            foreach ($erreurs as $e) $this->addFlash('error', $e);
            return $this->redirectToRoute('app_cart_index');
        }

        /** @var \App\Entity\Client $client */
        $client  = $this->getUser();
        $adresse = new Adresse();
        $adresse->setClient($client);

        $form = $this->createForm(AdresseLivraisonType::class, $adresse, [
            'adresses_existantes' => $client->getAdresses()->toArray(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'utilisateur a choisi une adresse existante
            $existante = $form->has('adresse_existante')
                ? $form->get('adresse_existante')->getData()
                : null;

            if ($existante instanceof Adresse) {
                $adresse = $existante;
            } else {
                // Sauvegarder la nouvelle adresse si demandé
                if ($form->get('sauvegarder')->getData()) {
                    $this->em->persist($adresse);
                    $this->em->flush();
                }
            }

            // Stocker l'id adresse en session pour l'étape suivante
            $request->getSession()->set('checkout_adresse_id', $adresse->getIdAddr() ?? null);
            $request->getSession()->set('checkout_adresse_data', [
                'type'  => $adresse->getTypeAddr(),
                'rue'   => $adresse->getRueAddr(),
                'ville' => $adresse->getVilleAddr(),
                'cp'    => $adresse->getCpAddr(),
                'pays'  => $adresse->getPaysAddr(),
            ]);

            return $this->redirectToRoute('app_checkout_livraison');
        }

        return $this->render('checkout/adresse.html.twig', [
            'form'  => $form,
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal(),
            'etape' => 1,
        ]);
    }

    // ── ÉTAPE 2 : Choix de la livraison ──────────────────────────────────────
    #[Route('/livraison', name: 'livraison')]
    public function livraison(Request $request): Response
    {
        if ($this->cart->isEmpty() || !$request->getSession()->has('checkout_adresse_data')) {
            return $this->redirectToRoute('app_checkout_adresse');
        }

        $form = $this->createForm(LivraisonChoixType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livraison = $form->get('livraison')->getData();
            $request->getSession()->set('checkout_livraison_id', $livraison->getIdLivr());
            return $this->redirectToRoute('app_checkout_paiement');
        }

        return $this->render('checkout/livraison.html.twig', [
            'form'  => $form,
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal(),
            'etape' => 2,
        ]);
    }

    // ── ÉTAPE 3 : Paiement + création de la commande ─────────────────────────
    #[Route('/paiement', name: 'paiement')]
    public function paiement(Request $request): Response
    {
        $session = $request->getSession();

        if ($this->cart->isEmpty()
            || !$session->has('checkout_adresse_data')
            || !$session->has('checkout_livraison_id')
        ) {
            return $this->redirectToRoute('app_checkout_adresse');
        }

        $form = $this->createForm(PaiementChoixType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier une dernière fois les stocks
            $erreurs = $this->cart->checkStock();
            if ($erreurs) {
                foreach ($erreurs as $e) $this->addFlash('error', $e);
                return $this->redirectToRoute('app_cart_index');
            }

            /** @var \App\Entity\Client $client */
            $client    = $this->getUser();
            $paiement  = $form->get('paiement')->getData();
            $livraison = $this->em->find(\App\Entity\Livraison::class, $session->get('checkout_livraison_id'));

            // Récupérer ou reconstruire l'adresse
            $adresseId = $session->get('checkout_adresse_id');
            if ($adresseId) {
                $adresse = $this->em->find(Adresse::class, $adresseId);
            } else {
                // Créer une adresse temporaire (non persistée)
                $data    = $session->get('checkout_adresse_data');
                $adresse = (new Adresse())
                    ->setClient($client)
                    ->setTypeAddr($data['type'])
                    ->setRueAddr($data['rue'])
                    ->setVilleAddr($data['ville'])
                    ->setCpAddr($data['cp'])
                    ->setPaysAddr($data['pays']);
                $this->em->persist($adresse);
            }

            // ── Créer la commande ──────────────────────────────────────────
            $commande = new Commande();
            $commande->setClient($client);
            $commande->setStatutCde('en_attente');
            $commande->setEstPayeCde(false);
            $commande->setLivraison($livraison);
            $commande->setPaiement($paiement);
            $commande->setAdresseLivraison($adresse);
            $this->em->persist($commande);

            // ── Créer les lignes de commande & décrémenter les stocks ──────
            $montant = '0.00';
            foreach ($this->cart->getItems() as $item) {
                /** @var \App\Entity\Produit $produit */
                $produit  = $item['produit'];
                $quantite = $item['quantite'];

                $ligne = new LigneCommande();
                $ligne->setCommande($commande);
                $ligne->setProduit($produit);
                $ligne->setQuantite($quantite);
                $ligne->setReduction('0.00');
                $this->em->persist($ligne);

                // Décrémenter le stock
                $produit->setStockProd($produit->getStockProd() - $quantite);

                $montant = bcadd($montant, $item['sousTotal'], 2);
            }

            // Ajouter les frais de livraison au montant
            if ($livraison?->getFraisLivr()) {
                $montant = bcadd($montant, $livraison->getFraisLivr(), 2);
            }

            $commande->setMontantCde($montant);
            $this->em->flush();

            // ── Nettoyer la session ────────────────────────────────────────
            $this->cart->clear();
            $session->remove('checkout_adresse_id');
            $session->remove('checkout_adresse_data');
            $session->remove('checkout_livraison_id');

            $this->addFlash('success', 'Commande passée avec succès !');
            return $this->redirectToRoute('app_checkout_confirmation', [
                'id' => $commande->getIdCde(),
            ]);
        }

        // Récupérer les données des étapes précédentes pour le récapitulatif
        $adresseData = $session->get('checkout_adresse_data');
        $livraison   = $this->em->find(\App\Entity\Livraison::class, $session->get('checkout_livraison_id'));

        return $this->render('checkout/paiement.html.twig', [
            'form'        => $form,
            'items'       => $this->cart->getItems(),
            'total'       => $this->cart->getTotal(),
            'adresseData' => $adresseData,
            'livraison'   => $livraison,
            'etape'       => 3,
        ]);
    }

    // ── CONFIRMATION ─────────────────────────────────────────────────────────
    #[Route('/confirmation/{id}', name: 'confirmation', requirements: ['id' => '\d+'])]
    public function confirmation(int $id): Response
    {
        $commande = $this->em->find(Commande::class, $id);

        if (!$commande || $commande->getClient()->getIdCli() !== $this->getUser()->getIdCli()) {
            throw $this->createNotFoundException();
        }

        return $this->render('checkout/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }
}