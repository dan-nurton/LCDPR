<?php

namespace App\Controller;

use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use DateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class CommentController extends Controller
{
    private $commentManager;
    private $authorManager;
    private $blogManager;
    private $feed;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->authorManager = new AuthorManager($entityManager);
        $this->commentManager = new CommentManager($entityManager);
        $this->blogManager = new blogManager($entityManager);
        $this->feed = $this->feed = new feedIoController();
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
        $comments = $this->commentManager->findComments($blogPostId);
        $blogPost = $this->blogManager->find($blogPostId);
        if(count($comments) < 6){
            throw new NotFoundHttpException();
        }
        foreach ($comments as $comment){
            if($comment->getBlogPost() != $blogPost){
                throw new NotFoundHttpException();
            }
        }
        return $this->render('comment/display_comments.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'author' => $this->authorManager->findUser($this->getUser()->getUserName()),
            'rss' => $this->feed->getRss()
        ));
    }

    /**
     * @Route("admin/comment/creation/{blogPostId}/{slug}/{route}", name="create_comment")
     * @param $blogPostId
     * @param $slug
     * @param $route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCommentAction($blogPostId,$slug,$route)
    {
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        $blogPost =  $this->blogManager->find($blogPostId);
        if(isset($_POST['comment']) &&  !empty($_POST['comment'])) {
            $commentData = [
                'content' => strip_tags($_POST['comment']),
                'author' => $author,
                'blogPost' => $blogPost
            ];
            $comment = $this->commentManager->hydrate($commentData);
            $this->commentManager->save($comment);
        }

        if ($route == 'display_comments') {
            return $this->redirectToRoute('display_comments', array(
                'blogPostId' => $blogPostId,
            ));
        } else {
            return $this->redirectToRoute('display_review', array(
                'blogPostId' => $blogPostId,
                'slug' => $slug
            ));
        }
    }

    /**
     * @Route("admin/supprimer-commentaire/{blogPostId}/{commentId}/{slug}/{route}", name="delete_comment")
     * @param $blogPostId
     * @param $commentId
     * @param $slug
     * @param $route
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCommentAction($blogPostId,$commentId,$slug,$route)
    {
        $comment = $this->commentManager->find($commentId);
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        if($comment->getAuthor() != $author && !$author->isAdmin()){
            throw new NotFoundHttpException();
        }
        $this->commentManager->remove($comment);
        $this->addFlash('success', 'Le commentaire a été effacé!');
        if ($route == 'display_comments') {
            return $this->redirectToRoute('display_comments', array(
                'blogPostId' => $blogPostId,
            ));
        } else {
            return $this->redirectToRoute('display_review', array(
                'blogPostId' => $blogPostId,
                'slug' => $slug
            ));
        }
    }

    /**
     * @Route("admin/comment/update/{blogPostId}/{commentId}/{slug}/{route}", name="update_comment")
     * @param $blogPostId
     * @param $commentId
     * @param $slug
     * @param $route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function UpdateCommentAction($blogPostId,$commentId,$slug,$route)
    {
        $comment = $this->commentManager->find($commentId);
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        if($comment->getAuthor() != $author){
            throw new NotFoundHttpException();
        }
        if(isset($_POST['update_comment']) && !empty($_POST['update_comment'])){
            $commentData = [
                'content' => strip_tags($_POST['update_comment']),
                'UpdatedAt' => new DateTime(),
            ];
            $this->commentManager->update($comment, $commentData);
        }
        if ($route == 'display_comments') {
            return $this->redirectToRoute('display_comments', array(
                'blogPostId' => $blogPostId,
            ));
        } else {
            return $this->redirectToRoute('display_review', array(
                'blogPostId' => $blogPostId,
                'slug' => $slug
            ));
        }
    }
}
