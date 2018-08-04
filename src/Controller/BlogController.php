<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
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
    public function displayReview($blogPostId,$slug)
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

    //page auteur
    /**
     * @Route("/author/{name}", name="author")
     */
    public function authorAction($name)
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
    public function  getBooksData(){
        $url= "https://www.googleapis.com/books/v1/volumes?q=";
        $isbn = $_POST['isbn'];
        $character = array('&', '<', '>', '/', '-','_'," ","$");
        $isbn = str_replace($character,"",$isbn);
        if(is_numeric($isbn)){
            if( strlen($isbn) == 10 || strlen($isbn) == 13 ){
                $book = $url.$isbn;
                $json = file_get_contents($book);
                $json_data = json_decode($json, true);
            }
            else{
                $this->addFlash('erreur', 'isbn non valide');
                return $this->render('author/review_form.html.twig');
            }
        }
        else{
            $this->addFlash('erreur', 'isbn non valide');
            return $this->render('author/review_form.html.twig');
            }

        if(isset($json_data['items'])){
            if(!isset( $json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])){
                $cover ="https://vignette.wikia.nocookie.net/main-cast/images/5/5b/Sorry-image-not-available.png/revision/latest/scale-to-width-down/480?cb=20160625173435";
            }
            else{
                $cover = $json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['description'])){
                $description ="Pas de description disponible";
            }
            else{
                $description =  $json_data['items'][0]['volumeInfo']['description'];
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['title'])){
                $title = "Pas de titre disponible";
            }
            else{
                $title = $json_data['items'][0]['volumeInfo']['title'];
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['categories'])){
                $category = "Catégorie non définie";
            }
            else{
                $category = $json_data['items'][0]['volumeInfo']['categories'][0];
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['authors'])){
                $writer = "Auteur non défini";
            }
            else{
                $writer = $json_data['items'][0]['volumeInfo']['authors'][0];
            }
            if(isset($_POST['avis']) && !empty($_POST['avis'])){
                $review = $_POST['avis'];
            }
            else{

                return $this->render('author/review_form.html.twig');
            }
        }
        else{
            $this->addFlash('erreur', 'livre non reconnu');
            return $this->render('author/review_form.html.twig');
        }
        var_dump($json_data['items'][0]);

        // si livre existe déjà
        $reviews = $this->blogPostRepository->findAll();
        $page = 1;
        foreach ($reviews as $search) {
            if ($search->getTitle() == $title) {
                $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
                $comments = $this->commentRepository->getAllCommentsWithLimit($search->getId(), $page, self::POST_LIMIT);
                $countComment = $this->commentRepository->getCountComment($search->getId());
                $this->message = 'Ce livre existe déjà. Vous pouvez poster un commentaire si vous le souhaitez';
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
        //instanciation BlogPost, hydratation
        $blogPost = new BlogPost();
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $blogPost->setAuthor($author);
        $blogPost->setReview($review);
        $blogPost->setTitle($title);
        $blogPost->setWriter($writer);
        $blogPost->setCover($cover);
        $blogPost->setDescription($description);
        $blogPost->setCategory($category);
        $this->entityManager->persist($blogPost);
        $this->entityManager->flush($blogPost);
        $slug = str_replace(' ', '_',  $json_data['items'][0]['volumeInfo']['title']);
        $slug .= '_'.$blogPost->getId();
        $blogPost->setSlug($slug);
        $this->entityManager->persist($blogPost);
        $this->entityManager->flush($blogPost);
        return $this->redirectToRoute('homepage');
    }

    // fonction recherche livre par titre
    /**
     * @Route("/search-review}", name="search_review")
     */
    public function  searchReview(){

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
    public function  searchIndex($letter){

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
