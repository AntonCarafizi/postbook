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

    private $queryBuilder;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
        $this->queryBuilder = $this->createQueryBuilder('p');
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */

    /**
     * @param $value
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findByFilter($value, $dateFormat, $order = 'DESC')
    {
        $this->queryBuilder->where('1 = 1');
        if (!empty($value['search'])) {
            $this->queryBuilder
                ->setParameter('search', '%'.$value['search'].'%')
                ->andWhere('p.title LIKE :search')
                ->orWhere('p.keywords LIKE :search');
        }

        if (!empty($value['from']) && empty($value['to'])) {
            $this->queryBuilder
                ->setParameter('from', $value['from'])
                ->andWhere('p.createdAt >= :from');
        }

        if (!empty($value['to'])) {
            $dateTo = \DateTime::createFromFormat($dateFormat, $value['to']);
            $date = new \DateTime();
            $days = $dateTo->diff(new \DateTime())->days;
            if ($days == 0) {
                $value['to'] = $date;
            }
            $this->queryBuilder
                ->setParameter('to', $value['to']);


            if (empty($value['from'])) {
                $this->queryBuilder->andWhere('p.createdAt <= :to');
            }

            if (!empty($value['from'])) {
                $this->queryBuilder
                    ->setParameter('from', $value['from']);
                if ($value['from'] == $value['to']) {
                    $this->queryBuilder->andWhere('p.createdAt = :from');
                }

                if ($value['from'] != $value['to']) {
                    $this->queryBuilder->andWhere('p.createdAt BETWEEN :from AND :to');
                }
            }
        }

        $this->queryBuilder->orderBy('p.createdAt', $order)
            ->getQuery()
            ->getResult();

        return $this->queryBuilder;
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
