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
        foreach ($commentData as $key => $value) {
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
            $method = 'set' . ucfirst($key);
            $comment-> $method($value);
        }
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

    public function findCommentsWithLimit($blogPostId,$limit){
        $comments = $this->commentRepository->getAllCommentsWithLimit($blogPostId,$limit);
        return $comments;
    }

    public function countComment($blogPostId){
        $count =  $this->commentRepository->getCountComment($blogPostId);
        return $count;
    }
}