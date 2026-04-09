namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/compte')]
class CompteController extends AbstractController
{
    #[Route('', name: 'app_compte')]
    public function index(): Response
    {
        /** @var \App\Entity\Client $client */
        $client = $this->getUser();
        return $this->render('compte/index.html.twig', ['client' => $client]);
    }

    #[Route('/commandes', name: 'app_compte_commandes')]
    public function commandes(): Response
    {
        $client = $this->getUser();
        return $this->render('compte/commandes.html.twig', [
            'commandes' => $client->getCommandes(),
        ]);
    }

    #[Route('/avis', name: 'app_compte_avis')]
    public function avis(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Client $client */
        $client = $this->getUser();
        return $this->render('compte/avis.html.twig', [
            'avis' => $client->getAvis(),
        ]);
    }

    #[Route('/avis/supprimer/{id}', name: 'app_compte_avis_delete', methods: ['POST'])]
    public function deleteAvis(int $id, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Client $client */
        $client = $this->getUser();
        $avis = $em->find(\App\Entity\Avis::class, $id);

        if ($avis && $avis->getClient()->getId() === $client->getId()) {
            $em->remove($avis);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }

        return $this->redirectToRoute('app_compte_avis');
    }
}