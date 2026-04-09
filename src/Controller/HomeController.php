namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepo, Request $request): Response
    {
        $q = $request->query->get('q');
        if ($q) {
            return $this->redirectToRoute('app_produits', ['q' => $q]);
        }

        return $this->render('home/index.html.twig', [
            'produitsVedette' => $produitRepo->findFeatured(8),
        ]);
    }
}