<?php
// src/Repository/ClientRepository.php
namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Trouve un client par email (utilisé par le provider de sécurité).
     */
    public function findByEmail(string $email): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.mailCli = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Charge un client avec toutes ses relations en une seule requête.
     */
    public function findOneWithRelations(int $id): ?Client
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.adresses', 'a')->addSelect('a')
            ->leftJoin('c.commandes', 'cmd')->addSelect('cmd')
            ->leftJoin('c.avis', 'av')->addSelect('av')
            ->leftJoin('c.typeClient', 't')->addSelect('t')
            ->where('c.idCli = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche des clients par nom, prénom ou email (utile pour l'admin).
     */
    public function search(string $q): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nomCli LIKE :q OR c.prenomCli LIKE :q OR c.mailCli LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('c.nomCli', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les clients inscrits récemment.
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.idCli', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}