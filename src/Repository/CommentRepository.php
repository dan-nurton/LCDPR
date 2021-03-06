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

/*SELECT * from comment WHERE blog_post_id = $blogPostId ORDER BY updated_at DESC LIMIT 5*/
    /**
     * @param $blogPostId
     * @param int $limit
     * @return mixed
     */
    public function getAllCommentsWithLimit($blogPostId, $limit){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('App:Comment', 'c')
            ->where('c.blogPost = :blogPost_id')->setParameter('blogPost_id',$blogPostId)
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * from comment WHERE blog_post_id = $blogPostId ORDER BY updated_at*/
    /**
     * @param $blogPostId
     * @return mixed
     */
    public function getAllComments($blogPostId){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('App:Comment', 'c')
            ->where('c.blogPost = :blogPost_id')->setParameter('blogPost_id',$blogPostId)
            ->orderBy('c.updatedAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

/*
 *  SELECT COUNT(*) from comment
    WHERE blog_post_id = $blogPostId
 */
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
