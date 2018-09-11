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

    /*SELECT * from blog_post
      ORDER BY id DESC
      LIMIT $limit */
    /**
 * @param int $limit
 *
 * @return array
 */
    public function getAllPostsWithLimit($limit)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->orderBy('bp.id', 'DESC')
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * from blog_post
     ORDER BY id DESC*/
    /**
     * @return array
     */
    public function getAllPosts()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->orderBy('bp.id', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * from blog_post
     INNER JOIN comment ON blog_post.id = comment.blog_post_id*/
    /**
     * @return array
     */
    public function getAllPostsWithComments(){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp','c')
            ->from('App:BlogPost', 'bp')
            ->join('bp.comments','c','WITH', 'bp.id=c.blogPost');

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT COUNT(*) FROM blog_post*/
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

    /*SELECT COUNT(*) FROM blog_post
    WHERE author_id = $author*/
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

    /*SELECT * FROM blog_post
    WHERE title LIKE  '''.$letter/'%''*/
    /**
     * @param $letter
     * @return array
     */
    public function searchByIndex($letter)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->where('bp.title LIKE :letter')->setParameter('letter',$letter.'%');

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * FROM blog_post WHERE title LIKE '$search' OR category LIKE '$search' OR writer LIKE '$search'*/
    /**
     * @param $search
     * @return array
     */
    public function searchByTitle($search)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->where('bp.title LIKE :search OR bp.category LIKE :search  OR bp.writer LIKE :search')->setParameter('search', '%'.$search.'%');

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * from blog_post
     INNER JOIN comment ON blog_post.id = comment.blog_post_id
     ORDER BY comment.updated_at DESC
     LIMIT $limit*/
    /**
     * @param int $limit
     * @return array
     */
    public function getAllPostsWithNewComment($limit){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->join('bp.comments','c','WITH', 'bp.id=c.blogPost')
            ->orderBy('c.updatedAt','DESC')
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /*SELECT * from blog_post
    INNER JOIN comment ON blog_post.id = comment.blog_post_id
    GROUP BY blog_post.title
    ORDER BY COUNT(comment.blog_post_id) DESC
    LIMIT $limit*/
    /**
 * @param int $limit
 * @return array
 */
    public function getAllPostsMostCommented($limit){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('bp')
            ->from('App:BlogPost', 'bp')
            ->join('bp.comments','c','WITH', 'bp.id=c.blogPost')
            ->groupBy('bp.title')
            ->orderBy('count(c.blogPost)','DESC')
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}
