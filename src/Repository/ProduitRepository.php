namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function searchWithFilters(
        ?string $q,
        ?int $typeId,
        ?float $prixMin,
        ?float $prixMax,
        string $sort = 'nomProd',
        string $order = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.typeProduit', 't')
            ->addSelect('t');

        if ($q) {
            $qb->andWhere('p.nomProd LIKE :q OR p.descProd LIKE :q OR p.refProd LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }
        if ($typeId) {
            $qb->andWhere('t.id = :typeId')->setParameter('typeId', $typeId);
        }
        if ($prixMin !== null) {
            $qb->andWhere('p.prixProd >= :prixMin')->setParameter('prixMin', $prixMin);
        }
        if ($prixMax !== null) {
            $qb->andWhere('p.prixProd <= :prixMax')->setParameter('prixMax', $prixMax);
        }

        $allowedSorts = ['nomProd', 'prixProd'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'nomProd';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('p.' . $sort, $order);

        return $qb->getQuery()->getResult();
    }

    public function findFeatured(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.stockProd > 0')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}