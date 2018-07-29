<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class CommentController extends Controller
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

    /**
     * @Route("/comment", name="comment")
     */
    public function index()
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/commentaires/{id}", name="display_comments")
     */
    public function displayReviews($id)
    {
         $comments = $this->commentRepository->getAllComments($id);

        return $this->render('comment/display_comments.html.twig', [
            'comments' => $comments,
            'id' => $id
        ]);
    }

    /**
     * @Route("/comment/creation/{id}", name="post_comment")
     * @param $id
     */
    public function createCommentAction($id)
    {
        $comment = new Comment();
        $blogPost = $this->blogPostRepository->find($id);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $comment->setContent($_POST['comment']);
        $comment->setAuthor($author);
        $comment->setBlogPost($blogPost);
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        $comments = $this->commentRepository->getAllComments($id);

        return $this->render('comment/display_comments.html.twig', [
            'comments' => $comments,
            'id' => $id
        ]);


    }




}
