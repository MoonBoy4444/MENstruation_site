<?php
// src/Controller/ProduitController.php
namespace App\Controller;

use App\Entity\Avis;
use App\Form\{AvisType, FiltreProduitsType};
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProduitController extends AbstractController
{
    #[Route('/produits', name: 'app_produits')]
    public function liste(Request $request, ProduitRepository $repo): Response
    {
        $form = $this->createForm(FiltreProduitsType::class);
        $form->handleRequest($request);

        $q       = $request->query->get('q');
        $typeId  = null;
        $prixMin = null;
        $prixMax = null;
        $sort    = 'nomProd';
        $order   = 'ASC';

        if ($form->isSubmitted() && $form->isValid()) {
            $data    = $form->getData();
            $q       = $data['q'] ?? $q;
            $typeId  = $data['type']?->getIdTypeProd();
            $prixMin = $data['prixMin'];
            $prixMax = $data['prixMax'];
            if ($data['sort'] === 'prixProd_desc') { $sort = 'prixProd'; $order = 'DESC'; }
            elseif ($data['sort'] === 'prixProd_asc') { $sort = 'prixProd'; $order = 'ASC'; }
        }

        $produits = $repo->searchWithFilters($q, $typeId, $prixMin, $prixMax, $sort, $order);

        return $this->render('produit/liste.html.twig', [
            'produits'    => $produits,
            'filtreForm'  => $form,
            'searchQuery' => $q,
        ]);
    }

    #[Route('/produits/{id}', name: 'app_produit_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProduitRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $produit = $repo->find($id);
        if (!$produit) { throw $this->createNotFoundException(); }

        $avisForm = null;
        $dejaCommente = false;

        if ($this->getUser()) {
            /** @var \App\Entity\Client $client */
            $client = $this->getUser();
            $dejaCommente = $produit->getAvis()->exists(
                fn($k, $a) => $a->getClient()->getIdCli() === $client->getIdCli()
            );

            if (!$dejaCommente) {
                $avis = new Avis();
                $avisForm = $this->createForm(AvisType::class, $avis);
                $avisForm->handleRequest($request);
                $avis->setDateAvisValue();

                if ($avisForm->isSubmitted() && $avisForm->isValid()) {
                    $avis->setClient($client);
                    $avis->setProduit($produit);
                    $em->persist($avis);
                    $em->flush();
                    $this->addFlash('success', 'Merci pour votre avis !');
                    return $this->redirectToRoute('app_produit_show', ['id' => $id]);
                }
            }
        }

        return $this->render('produit/show.html.twig', [
            'produit'      => $produit,
            'avisForm'     => $avisForm,
            'dejaCommente' => $dejaCommente,
        ]);
    }
}