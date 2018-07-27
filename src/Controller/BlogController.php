<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BlogPost;
use App\Form\EntryFormType;


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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }

    /**
     * @Route("/", name="homepage")
<<<<<<< HEAD
     * @Route("/critiques", name="display_reviews")
=======
     * @Route("/reviews", name="reviews")
>>>>>>> devDam
     */
    public function entriesAction(Request $request)
    {
        $page = 1;

        if ($request->get('page')) {
            $page = $request->get('page');
        }
        return $this->render('blog/display_reviews.html.twig', [
            'blogPosts' => $this->blogPostRepository->getAllPosts($page, self::POST_LIMIT),
            'totalBlogPosts' => $this->blogPostRepository->getPostCount(),
            'page' => $page,
            'entryLimit' => self::POST_LIMIT
        ]);
    }

    /**
<<<<<<< HEAD
     * @Route("/critique/{slug}", name="display_review")
=======
     * @Route("/entry/{slug}", name="review")
>>>>>>> devDam
     */
    public function entryAction($slug)
    {
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);

        if (!$blogPost) {
            $this->addFlash('error', 'Article introuvable...');


            return $this->redirectToRoute('display_reviews');

        }

        return $this->render('blog/display_review.html.twig', array(
            'blogPost' => $blogPost
        ));
    }

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

                return $this->render('author/entry_form.html.twig');
            }
        }
        else{
            return $this->render('author/entry_form.html.twig');

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

                return $this->render('author/entry_form.html.twig');
            }
        }
        else{
            return $this->render('author/entry_form.html.twig');
        }
        var_dump($json_data['items'][0]);

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
}
