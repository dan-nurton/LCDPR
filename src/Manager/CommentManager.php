<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 04/08/2018
 * Time: 17:22
 */

namespace App\Manager;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BlogPost;

class CommentManager
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

    public function hydrate($commentData){
        $comment = new Comment();
        //on passe a $data un tableau avec des clés correspondant aux attributs
        foreach ($commentData as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set' . ucfirst($key);
            $comment-> $method($value);
        }
        return $comment;
    }

    public function save($comment){
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
    }

    public function remove($comment){
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }

    public function update($comment,$commentData){
        foreach ($commentData as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set' . ucfirst($key);
            $comment-> $method($value);
        }
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        return $comment;
    }

    public function find($commentId){
        $comment = $this->commentRepository->find($commentId);
        return $comment;
    }

    public function findComments($blogPostId){
       $comments = $this->commentRepository->getAllComments($blogPostId);
        return $comments;
    }

    public function findCommentsWithLimit($blogPostId,$page,$limit){
        $comments = $this->commentRepository->getAllCommentsWithLimit($blogPostId, $page, $limit);
        return $comments;
    }

    public function countComment($blogPostId){
        $count =  $this->commentRepository->getCountComment($blogPostId);
        return $count;
    }
}