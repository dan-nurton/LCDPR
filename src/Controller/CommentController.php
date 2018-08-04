<?php

namespace App\Controller;

use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class CommentController extends Controller
{
    private $commentManager;
    private $authorManager;
    private $blogManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->authorManager = new AuthorManager($entityManager);
        $this->commentManager = new CommentManager($entityManager);
        $this->blogManager = new blogManager($entityManager);
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
        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $this->blogManager->find($blogPostId),
            'comments' => $this->commentManager->findComments($blogPostId),
            'author' => $this->authorManager->findUser($this->getUser()->getUserName())
        ));
    }

    /**
     * @Route("admin/comment/creation/{blogPostId}", name="create_comment")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCommentAction($blogPostId)
    {
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        $blogPost =  $this->blogManager->find($blogPostId);
        $commentData = [
            'content' => $_POST['comment'],
            'author' => $author,
            'blogPost' => $blogPost
        ];
       $comment = $this->commentManager->hydrate($commentData);
       $this->commentManager->save($comment);

        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $this->commentManager->findComments($blogPostId),
            'countComment' => $this->commentManager->countComment($blogPostId),
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
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        $blogPost = $this->blogManager->find($blogPostId);
        $commentData = [
            'content' => $_POST['comment'],
            'author' => $author,
            'blogPost' => $blogPost
        ];
        $comment = $this->commentManager->hydrate($commentData);
        $this->commentManager->save($comment);

        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $this->commentManager->findComments($blogPostId),
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
        $comment = $this->commentManager->find($commentId);
        $this->commentManager->remove($comment);
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
        $comment = $this->commentManager->find($commentId);
        $commentData = [
            'content' => $_POST['update_comment'],
            'UpdatedAt' => new DateTime(),
        ];
        $this->commentManager->update($comment,$commentData);
        return $this->redirectToRoute('display_comments', array(
            'blogPostId' => $blogPostId,
            ));
    }
}
