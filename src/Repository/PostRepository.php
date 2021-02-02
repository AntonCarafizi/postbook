<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */

    public function findByFilter($value)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('1 = 1');
        if (!empty($value['search'])) {
            $queryBuilder
                ->setParameter('search', '%'.$value['search'].'%')
                ->andWhere('p.title LIKE :search')
                ->orWhere('p.keywords LIKE :search');
        }

        if (!empty($value['from']) && empty($value['to'])) {
            $queryBuilder
                ->setParameter('from', $value['from'])
                ->andWhere('p.createdAt >= :from');
        }

        if (!empty($value['to']) && empty($value['from'])) {
            $queryBuilder
                ->setParameter('to', $value['to'])
                ->andWhere('p.createdAt <= :to');

        }

        if (!empty($value['from']) && !empty($value['to'])) {

            $queryBuilder
                ->setParameter('from', $value['from'])
                ->setParameter('to', $value['to']);
            if ($value['from'] == $value['to']) {
                $queryBuilder->andWhere('p.createdAt = :from');
            }

            if ($value['from'] != $value['to']) {
                $queryBuilder->andWhere('p.createdAt BETWEEN :from AND :to');
            }
        }

        $queryBuilder->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $queryBuilder;
    }



    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
