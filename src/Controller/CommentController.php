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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayAllComment($id){
        $blogPost = $this->blogPostRepository->find($id);
        $comments = $this->commentRepository->getAllComments($id);
        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
        ));

    }


    /**
     * @Route("/comment/creation/{id}", name="post_comment")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
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
        $countComment = $this->commentRepository->getCountComment($id);

        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'countComment' => $countComment,
            'id' => $id,
        ));

    }




}
