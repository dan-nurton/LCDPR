<?php

namespace App\Controller;


use App\ApiBooks;
use App\Manager\BlogManager;
use Doctrine\ORM\EntityManagerInterface;
use mysqli;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BlogPost;



class BlogController extends Controller
{
    /** @var integer */
    const POST_LIMIT = 5;

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

    //page accueil
    /**
     * @Route("/", name="homepage")
     * @Route("/critiques", name="display_reviews")
     */
    public function displayReviewsAction(Request $request)
    {
        $page = 1;
        $blogPosts = $this->blogPostRepository->getAllPostsForAdmin($page, self::POST_LIMIT);
        if ($request->get('page')) {
            $page = $request->get('page');
        }
        return $this->render('blog/display_reviews.html.twig', [
            'blogPosts' => $blogPosts,
            'totalBlogPosts' => $this->blogPostRepository->getPostCount(),
            'page' => $page,
            'entryLimit' => self::POST_LIMIT,
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
        $page = 1;
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);
        $comments = $this->commentRepository->getAllCommentsWithLimit($blogPostId, $page, self::POST_LIMIT);
        $countComment = $this->commentRepository->getCountComment($blogPostId);
        if (!$blogPost) {
            $this->addFlash('error', 'Article introuvable...');
            return $this->redirectToRoute('display_reviews');
        }
        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost,
            'comments' => $comments,
            'countComment' => $countComment,
            'page' => $page,
            'entryLimit' => self::POST_LIMIT,
            'author' => $author,
        ));
    }

    /**
     * @Route("/creation-critique", name="create_review")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReviewAction()
    {
        return $this->render('author/review_form.html.twig');
    }

    //page auteur
    /**
     * @Route("/author/{name}", name="author")
     */
    public function displayAuthorAction($name)
    {
        $author = $this->authorRepository->findOneByUsername($name);
        if (!$author) {
            $this->addFlash('error', 'Auteur introuvable...');
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
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
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
                        $review = strip_tags ($_POST['avis']);
                        $book['review'] = $review;
                        $book['author']= $author;
                        $manager = new BlogManager();
                        $blogPost = $manager->hydrate($book);
                    }
                    else{
                        $this->addFlash('erreur', 'Isbn non valide. L\'ISBN doit etre un chiffre entre 10 ou 13 numéros');
                        return $this->render('author/review_form.html.twig');
                    }
                }
                else{
                    $this->addFlash('erreur', 'Isbn non valide. L\'ISBN doit etre un chiffre entre 10 ou 13 numéros');
                    return $this->render('author/review_form.html.twig');
                }
            }
            else{
                $this->addFlash('erreur', 'Pas d\'avis entré');
                return $this->render('author/review_form.html.twig');
            }
        }
        else{
            $this->addFlash('erreur', 'Pas d\'ISBN entré');
            return $this->render('author/review_form.html.twig');
        }

        // si livre existe déjà
        $title = $blogPost->getTitle();
        $review = $blogPost->getReview();
        $reviews = $this->blogPostRepository->findAll();
        $page = 1;
        foreach ($reviews as $search) {
            if ($search->getTitle() == $title) {
                $comments = $this->commentRepository->getAllCommentsWithLimit($search->getId(), $page, self::POST_LIMIT);
                $countComment = $this->commentRepository->getCountComment($search->getId());
                $this->addFlash('exist', 'Ce livre existe déjà. Vous pouvez poster un commentaire si vous le souhaitez');
                return $this->render('blog/display_review.html.twig', array(
                    'blogPost' => $search,
                    'comments' => $comments,
                    'countComment' => $countComment,
                    'id' => $search->getId(),
                    'page' => $page,
                    'entryLimit' => self::POST_LIMIT,
                    'author' => $author,
                    'commentReview' => $review
                ));
            }
        }
        $this->entityManager->persist($blogPost);
        $this->entityManager->flush($blogPost);
        return $this->redirectToRoute('homepage');
    }

    // fonction recherche livre par titre
    /**
     * @Route("/search-review}", name="search_review")
     */
    public function  searchReviewAction(){
        $search = strtolower ($_POST['search']);
        $reviews = $this->blogPostRepository->findAll();
        $result = [];
        foreach ($reviews as $review){
            if(strtolower($review->getTitle()) == $search){
                return $this->redirectToRoute('display_review',array(
                    'slug'=> $review->getSlug(),
                    'id' => $review->getId(),
                ));
            }
        }
    }

// fonction recherche par index
    /**
     * @Route("/search-index/{letter}", name="search_index")
     * @param $letter
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function  searchIndexAction($letter){
        $reviews = $this->blogPostRepository->searchByIndex($letter);
        $blogPosts = [];
        foreach ($reviews as $review){
            $blogPosts[] = $review;
        }
                return $this->render('blog/display_result_reviews.html.twig',array(
                    'blogPosts'=> $blogPosts
                ));
    }

}
