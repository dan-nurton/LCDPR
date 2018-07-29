<?php

namespace App\Repository;

use App\Entity\BlogPost;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BlogPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogPost[]    findAll()
 * @method BlogPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('b')
            ->where('b.something = :value')->setParameter('value', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
 * @param int $page
 * @param int $limit
 *
 * @return array
 */
    public function getAllPosts($page = 1, $limit = 5)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->orderBy('bp.id', 'DESC')
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
    /**
     * @return array
     */
    public function getAllPostsForAdmin()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->orderBy('bp.id', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @param $id
     * @return array
     */
    public function getAllPostsWithComments($page = 1, $limit = 5){

        /* SELECT *
          FROM blog_post
          INNER JOIN comment ON blog_post.id = comment.blog_post_id
        */
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp','c')
            ->from('App:BlogPost', 'bp')
            ->join('bp.comments','c','WITH', 'bp.id=c.blogPost');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPostCount()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('count(bp)')
            ->from('App:BlogPost', 'bp');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByAuthor($author)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('count(bp)')
            ->from('App:BlogPost', 'bp')
            ->where('bp.author = :author')->setParameter('author', $author);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
