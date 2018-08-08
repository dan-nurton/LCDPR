<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 04/08/2018
 * Time: 09:59
 */

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BlogPost;


class BlogManager
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $blogPostRepository;
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $commentRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
        $this->commentRepository = $entityManager->getRepository('App:Comment');
    }
    public function hydrate($book){
        $blogPost = new BlogPost();
        //on passe a $data un tableau avec des clés correspondant aux attributs
        foreach ($book as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set' . ucfirst($key);
            $blogPost-> $method($value);
        }
        return $blogPost;
    }

    public function save($blogPost){
        $this->entityManager->persist($blogPost);
        $this->entityManager->flush($blogPost);
    }

    public function remove($blogPost){
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();
    }

    public function findBlogPostBySlug($slug){
       $blogPost = $this->blogPostRepository->findOneBySlug($slug);
       return $blogPost;
    }
    public function findBlogPostsNewComment($limit){
        $blogPosts = $this->blogPostRepository->getAllPostsWithNewComment($limit);
        return $blogPosts;
    }
    public function findBlogPostsMostComment($limit){
        $blogPosts = $this->blogPostRepository->getAllPostsMostCommented($limit);
        return $blogPosts;
    }

    public function find($blogPostId){
      $blogPost = $this->blogPostRepository->find($blogPostId);
      return $blogPost;
    }

    public function findByAuthor($author){
        $blogPosts = $this->blogPostRepository->findByAuthor($author);
        return $blogPosts;
    }

    public function findAll(){
        $blogPosts = $this->blogPostRepository->findAll();
        return $blogPosts;
    }

    public function findBlogPostWithLimit($page,$limit){
        $blogPosts = $this->blogPostRepository->getAllPostsWithComments($page, $limit);
        return $blogPosts;
    }

    public function findByIndex($letter){
        $blogPosts =$this->blogPostRepository->searchByIndex($letter);
        return $blogPosts;
    }
    public function findByTitle($title){
        $blogPost =$this->blogPostRepository->searchByTitle($title);
        return $blogPost;
    }

    public function countBlogPosts(){
        $count =$this->blogPostRepository->getPostCount();
        return $count;
    }

    public function countByAuthor($author){
        $count =$this->blogPostRepository->countByAuthor($author);
        return $count;
    }

    public function findBlogPostForAdmin(){
        $blogPosts = $this->blogPostRepository->getAllPostsForAdmin();
        return $blogPosts;
    }
}