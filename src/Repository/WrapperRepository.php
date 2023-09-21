<?php

namespace App\Repository;

use App\Entity\Wrapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wrapper>
 *
 * @method Wrapper|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wrapper|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wrapper[]    findAll()
 * @method Wrapper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WrapperRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wrapper::class);
    }

//    /**
//     * @return RssWrapper[] Returns an array of RssWrapper objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RssWrapper
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
