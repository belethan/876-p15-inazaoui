<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Médias visibles (utilisateur actif uniquement)
     */
    public function findVisibleMedias(
        array $criteria = [],
        int $limit = null,
        int $offset = null
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.userActif = true')
            ->addOrderBy('m.id', 'ASC');

        // critères dynamiques (ex: user courant)
        foreach ($criteria as $field => $value) {
            $qb->andWhere("m.$field = :$field")
                ->setParameter($field, $value);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compteur de médias visibles
     */
    public function countVisibleMedias(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.userActif = true');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("m.$field = :$field")
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
