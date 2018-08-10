<?php

namespace App\Controller;

use App\ApiBooks;
use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->authorManager = new AuthorManager($entityManager);
        $this->commentManager = new CommentManager($entityManager);
        $this->blogManager = new blogManager($entityManager);
    }

    //page accueil
    /**
     * @Route("/", name="homepage")
     * @Route("/critiques/home", name="display_reviews_with_limits")
     */
    public function displayReviewsWithLimitAction()
    {
        $page = 1;
        $blogPosts = $this->blogManager->findBlogPostWithLimit($page, self::POST_LIMIT);
        $newBlogpostsComment = $this->blogManager->findBlogPostsNewComment(self::POST_LIMIT);
        $blogpostsMostCommented = $this->blogManager->findBlogPostsMostComment(self::POST_LIMIT);
        $rss = new feedIoController();
        $rss = $rss->getRss();

        return $this->render('blog/display_reviews.html.twig', [
            'blogPosts' => $blogPosts,
            'newBlogpostsComment'=> $newBlogpostsComment,
            'blogpostsMostCommented' => $blogpostsMostCommented,
            'rss' => $rss,
        ]);
    }
    /**
     * @Route("/critiques", name="display_reviews")
     */
    public function displayReviewsAction()
    {
        $rss = new feedIoController();
        $rss = $rss->getRss();
        $blogPosts = $this->blogManager->findAll();
        return $this->render('blog/display_all_reviews.html.twig', [
            'blogPosts' => $blogPosts,
            'rss'=>$rss
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
        $rss = new feedIoController();
        $rss = $rss->getRss();
        $blogPost = $this->blogManager->findBlogPostBySlug($slug);
        if (!$blogPost) {
            $this->addFlash('error', 'Article introuvable...');
            return $this->redirectToRoute('display_reviews');
        }
        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $this->commentManager->findCommentsWithLimit($blogPostId,self::POST_LIMIT),
            'countComment' => $this->commentManager->countComment($blogPostId),
            'author' => $this->authorManager->findUser($this->getUser()->getUserName()),
            'rss'=>$rss
        ));
    }

    /**
     * @Route("/formulaire-critique", name="display_form_review")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayReviewFormAction()
    {
        $rss = new feedIoController();
        $rss = $rss->getRss();
        return $this->render('author/review_form.html.twig',array(
            'rss'=>$rss
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
                $isbn = str_replace($character,"",$_POST['isbn']);
                $isbn = strip_tags($isbn);
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
        $rss = new feedIoController();
        $rss = $rss->getRss();
        $title = $blogPost->getTitle();
        $review = $blogPost->getReview();
        $reviews = $this->blogManager->findAll();
        $page = 1;
        foreach ($reviews as $search) {
            if ($search->getTitle() == $title) {
                $comments = $this->commentManager->findCommentsWithLimit($search->getId(), $page, self::POST_LIMIT);
                $countComment =$this->commentManager->countComment($search->getId());
                $this->addFlash('exist', 'Ce livre existe déjà. Vous pouvez poster un commentaire si vous le souhaitez');
                return $this->render('blog/display_review.html.twig', array(
                    'blogPost' => $search,
                    'comments' => $comments,
                    'countComment' => $countComment,
                    'id' => $search->getId(),
                    'page' => $page,
                    'entryLimit' => self::POST_LIMIT,
                    'author' => $author,
                    'commentReview' => $review,
                    'rss'=>$rss
                ));
            }
        }
        $this->blogManager->save($blogPost);
        return $this->redirectToRoute('homepage');
    }

    // fonction recherche livre par titre
    /**
     * @Route("/search-review}", name="search_review")
     */
    public function  searchReviewAction(){
        $title = strtolower ($_POST['search']);
        $blogPosts = $this->blogManager->findByTitle($title);
        $rss = new feedIoController();
        $rss = $rss->getRss();
        return $this->render('blog/display_result_reviews.html.twig',array(
            'blogPosts'=> $blogPosts,
            'rss'=>$rss
        ));
    }

// fonction recherche par index
    /**
     * @Route("/resultat_index/{letter}", name="search_index")
     * @param $letter
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function  searchIndexAction($letter){
        $rss = new feedIoController();
        $rss = $rss->getRss();
        $reviews = $this->blogManager->findByIndex($letter);
        $blogPosts = [];
        foreach ($reviews as $review){
            $blogPosts[] = $review;
        }
                return $this->render('blog/display_result_reviews.html.twig',array(
                    'blogPosts'=> $blogPosts,
                    'rss'=>$rss
                ));
    }

}
