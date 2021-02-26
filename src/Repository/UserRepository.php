<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);

    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */

    public function findOnline($cookieLifetime, $currentTimestamp)
    {
        return $this->createQueryBuilder('u')
            ->where(':current_timestamp - u.lastLogin <= :cookie_lifetime ')
            ->setParameter('cookie_lifetime', $cookieLifetime)
            ->setParameter('current_timestamp', $currentTimestamp)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function findByIdList($idList): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id IN (:id_list)')
            ->setParameter('id_list', $idList)
            ->orderBy('FIELD(u.id, :id_list)', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
