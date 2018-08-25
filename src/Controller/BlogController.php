<?php

namespace App\Controller;

use App\ApiBooks;
use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{
    /** @var integer */
    const POST_LIMIT = 5;
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
        $this->feed = new feedIoController();
    }

    //page accueil
    /**
     * @Route("/", name="homepage")
     * @Route("/critiques/home", name="display_reviews_with_limits")
     */
    public function displayReviewsWithLimitAction()
    {
        $blogPosts = $this->blogManager->findBlogPostWithLimit(self::POST_LIMIT);
        $newBlogpostsComment = $this->blogManager->findBlogPostsNewComment(self::POST_LIMIT);
        $blogpostsMostCommented = $this->blogManager->findBlogPostsMostComment(self::POST_LIMIT);

        return $this->render('blog/display_reviews.html.twig', [
            'blogPosts' => $blogPosts,
            'newBlogpostsComment'=> $newBlogpostsComment,
            'blogpostsMostCommented' => $blogpostsMostCommented,
            'rss' => $this->feed->getRss(),
        ]);
    }
    /**
     * @Route("/critiques", name="display_reviews")
     */
    public function displayReviewsAction()
    {
        $blogPosts = $this->blogManager->findAll();
        return $this->render('blog/display_all_reviews.html.twig', [
            'blogPosts' => $blogPosts,
            'rss'=>$this->feed->getRss()
        ]);
    }


    //page critique
    /**
     * @Route("admin/critique/{blogPostId}/{slug}", name="display_review")
     * @param $slug
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function displayReviewAction($blogPostId,$slug)
    {
        $comments = $this->commentManager->findCommentsWithLimit($blogPostId,self::POST_LIMIT);
        $blogPost = $this->blogManager->findBlogPostBySlug($slug);
        if (!$blogPost) {
            $this->addFlash('error', 'Article introuvable...');
            return $this->redirectToRoute('display_reviews');
        }

        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'countComment' => $this->commentManager->countComment($blogPostId),
            'author' => $this->authorManager->findUser($this->getUser()->getUserName()),
            'rss'=>$this->feed->getRss()
        ));
    }

    /**
     * @Route("/admin/formulaire-critique", name="display_form_review")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayReviewFormAction()
    {
        return $this->render('author/review_form.html.twig',array(
            'rss'=>$this->feed->getRss()
            ));
    }

    //page auteur
    /**
     * @Route("/author/{name}", name="author")
     * @param $name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function displayAuthorAction($name)
    {
        $author = $this->authorManager->findByName($name);
        if (!$author) {
            $this->addFlash('error', 'Auteur introuvable');
            return $this->redirectToRoute('display_reviews');
        }
        return $this->render('blog/display_author.html.twig', [
            'author' => $author
        ]);
    }

    // fonction récupération livre API
    /**
     * @Route("/get-book}", name="get_book")
     */
    public function  getBookAction(){

        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        // si il y a un ISBN posté
        if(isset($_POST['isbn']) && !empty($_POST['isbn'])){

            // si il y a un avis posté
            if(isset($_POST['avis']) && !empty($_POST['avis'])){

                $character = array('&', '/','-','_'," ");
                $isbn = strip_tags(str_replace($character,"",$_POST['isbn']));
                if(is_numeric($isbn)){
                    if( strlen($isbn) == 10 || strlen($isbn) == 13 ){
                        $book = new ApiBooks();
                        $book = $book->getBook($isbn);
                        if(!empty($book)){
                            $review = strip_tags ($_POST['avis']);
                            $book['review'] = $review;
                            $book['author']= $author;
                            $blogPost = $this->blogManager->hydrate($book);

                        }
                        else{
                            $this->addFlash('erreur', 'livre non reconnu...');
                            return $this->redirectToRoute('display_form_review');
                        }
                    }
                    else{
                        $this->addFlash('erreur', 'Isbn non valide...
                         L\'ISBN doit etre un chiffre entre 10 ou 13 numéros');
                        return $this->redirectToRoute('display_form_review');
                    }
                }
                else{
                    $this->addFlash('erreur', 'Isbn non valide...
                     L\'ISBN doit etre un chiffre entre 10 ou 13 numéros');
                    return $this->redirectToRoute('display_form_review');
                }
            }
            else{
                $this->addFlash('erreur', 'Pas d\'avis entré');
                return $this->redirectToRoute('display_form_review');
            }
        }
        else{
            $this->addFlash('erreur', 'Pas d\'ISBN entré');
            return $this->redirectToRoute('display_form_review');
        }


        // si livre existe déjà
        $title = $blogPost->getTitle();
        $review = $blogPost->getReview();
        $reviews = $this->blogManager->findAll();
        foreach ($reviews as $search) {
            if ($search->getTitle() == $title) {
                $comments = $this->commentManager->findCommentsWithLimit($search->getId(), self::POST_LIMIT);
                $countComment =$this->commentManager->countComment($search->getId());
                $this->addFlash('exist', 'Ce livre existe déjà. Vous pouvez poster un commentaire si vous le souhaitez');
                return $this->render('blog/display_review.html.twig', array(
                    'blogPost' => $search,
                    'comments' => $comments,
                    'countComment' => $countComment,
                    'id' => $search->getId(),
                    'entryLimit' => self::POST_LIMIT,
                    'author' => $author,
                    'commentReview' => $review,
                    'rss'=>$this->feed->getRss()
                ));
            }
        }
        $this->blogManager->save($blogPost);
        return $this->redirectToRoute('homepage');
    }

    // fonction recherche livre par titre/auteur/catégorie
    /**
     * @Route("/search-review}", name="search_review")
     */
    public function  searchReviewAction(){
        $title =  strip_tags(strtolower ($_POST['search']));
        $title = htmlentities( $title, ENT_NOQUOTES, 'utf-8' );
        $blogPosts = $this->blogManager->findByTitle($title);
        return $this->render('blog/display_result_reviews.html.twig',array(
            'blogPosts'=> $blogPosts,
            'rss'=>$this->feed->getRss()
        ));
    }

// fonction recherche par index
    /**
     * @Route("/resultat_index/{letter}", name="search_index")
     * @param $letter
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function  searchIndexAction($letter){

        if(ctype_alpha($letter) == false){
            throw new NotFoundHttpException();
        }
        $reviews = $this->blogManager->findByIndex($letter);

        $blogPosts = [];
        foreach ($reviews as $review){
            $blogPosts[] = $review;
        }
                return $this->render('blog/display_result_reviews.html.twig',array(
                    'blogPosts'=> $blogPosts,
                    'rss'=>$this->feed->getRss()
                ));
    }

}
