<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\Users;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    /** EntityManager $manager */
    private $manager;

    /** UserPasswordEncoderInterface $encoder */
    private $encoder;

    /**
     * UsersRepository constructor.
     * @param RegistryInterface $registry
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(RegistryInterface $registry, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct($registry, Users::class);

        $this->manager = $registry->getEntityManager();
        $this->encoder = $encoder;
    }

    /**
     * Create a new user
     * @param $data
     * @return Users
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewUser($data)
    {
        $user = new Users();
        $user->setEmail($data['email'])
            ->setPassword($this->encoder->encodePassword($user, $data['password']));

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}
