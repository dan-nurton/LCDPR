<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.something = :value')->setParameter('value', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    /**
     * @param $blogPostId
     *
     * @return mixed
     */
    public function getAllComments($blogPostId){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('App:Comment', 'c')
            ->where('c.blogPost = :blogPost_id')->setParameter('blogPost_id',$blogPostId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $blogPostId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountComment($blogPostId){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('count(c)')
            ->from('App:Comment', 'c')
            ->where('c.blogPost = :blogPost_id')->setParameter('blogPost_id',$blogPostId);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }





}
