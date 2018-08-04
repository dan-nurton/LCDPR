<?php

namespace App\Controller;

use DateTime;
use App\Entity\Author;
use App\Entity\Comment;
use http\Env\Response;
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
    public function indexAction()
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("admin/commentaires/{blogPostId}", name="display_comments")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayAllCommentAction($blogPostId){

        $blogPost = $this->blogPostRepository->find($blogPostId);
        $comments = $this->commentRepository->getAllComments($blogPostId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());

        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'author' => $author,
        ));
    }


    /**
     * @Route("admin/comment/creation/{blogPostId}", name="create_comment")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCommentAction($blogPostId)
    {
        $comment = new Comment();
        $blogPost = $this->blogPostRepository->find($blogPostId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $comment->setContent($_POST['comment']);
        $comment->setAuthor($author);
        $comment->setBlogPost($blogPost);
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        $comments = $this->commentRepository->getAllComments($blogPostId);
        $countComment = $this->commentRepository->getCountComment($blogPostId);

        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'countComment' => $countComment,
            'id' => $blogPostId,
            'author'=> $author
        ));
    }

    /**
     * @Route("admin/comment/creation/comment/{blogPostId}", name="create_comment_review")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCommentActionInCommentReviewsAction($blogPostId)
    {
        $comment = new Comment();
        $blogPost = $this->blogPostRepository->find($blogPostId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $comment->setContent($_POST['comment']);
        $comment->setAuthor($author);
        $comment->setBlogPost($blogPost);
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        $comments = $this->commentRepository->getAllComments($blogPostId);

        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'author' => $author,
        ));
    }


    /**
     * @Route("admin/supprimer-commentaire/{blogPostId}/{commentId}/{slug}", name="delete_comment")
     * @param $blogPostId
     * @param $commentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCommentAction($blogPostId,$commentId)
    {
        $comment = $this->commentRepository->find($commentId);
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le commentaire a été effacé!');
            return $this->redirectToRoute('display_comments', array(
                'blogPostId' => $blogPostId,
            ));
    }

    /**
     * @Route("admin/comment/update/{blogPostId}/{commentId}/{slug}", name="update_comment")
     * @param $blogPostId
     * @param $commentId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function UpdateCommentAction($blogPostId,$commentId)
    {
        $comment = $this->commentRepository->find($commentId);
        $comment->setContent($_POST['update_comment']);
        $comment->setUpdatedAt(new DateTime());
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);

        return $this->redirectToRoute('display_comments', array(
            'blogPostId' => $blogPostId,
            ));
    }
}
