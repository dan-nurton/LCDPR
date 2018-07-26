<?php
namespace App\Controller;
use App\Form\UpdateAllBlogFormType;
use App\Form\UpdateAuthorFormType;
use App\Form\UpdateBlogFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Form\AuthorFormType;
/**
 * @Route("/admin")
 */
class AuthorController extends Controller
{
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
     * @Route("/auteur/creation", name="author_create")
     */
    public function createAuthorAction(Request $request)
    {
        if ($this->authorRepository->findOneByUsername($this->getUser()->getUserName())) {
            // Redirect to dashboard.
            $this->addFlash('error', 'Unable to create author, author already exists!');
            return $this->redirectToRoute('homepage');
        }
        $author = new Author();
        $author->setUsername($this->getUser()->getUserName());
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($author);
            $this->entityManager->flush($author);
            $request->getSession()->set('user_is_author', true);
            $this->addFlash('success', 'Félicitation! Vous pouvez maintenant poster vos critiques!');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('author/create_author.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/creation-critique", name="author_create_review")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReviewAction(Request $request)
    {
        return $this->render('author/review_form.html.twig');
    }


    /**
     * @Route("/", name="admin_index")
     * @Route("/panel", name="admin_panel")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function panelAction()
    {
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $authors = [];
        $blogPosts = [];
        $blogPostsCounts = [];
        if ($author) {
            $blogPosts = $this->blogPostRepository->findByAuthor($author);
        }
        if($author->isAdmin()){
            $blogPosts = $this->blogPostRepository->getAllPostsForAdmin();
            $authors = $this->authorRepository->getAllAuthorsForAdmin();
            foreach($authors as $author){
                $blogPostsCounts [$author->getPseudo()]=  $this->blogPostRepository->countByAuthor($author);
            }
        }
        return $this->render('author/panel.html.twig', [
            'blogPosts' => $blogPosts,
            'author' => $author,
            'authors' => $authors,
            'blogPostsCounts' => $blogPostsCounts
        ]);
    }

    // supprimer crtique de l'utilisateur uniquement
    /**
     * @Route("/supprimer-critique-utilisateur/{entryId}", name="author_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorReviewAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('erreur', 'Supression impossible!');
            return $this->redirectToRoute('admin_panel');
        }
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }

    // supprimer critiques de tous les utilisateurs
    /**
     * @Route("/supprimer-toutes-critiques/{entryId}", name="admin_delete_all_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorAndReviewsAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }


    //update critique utilisateur
    /**
     * @Route("/update-entry/{entryId}", name="author_review_update_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateReview(Request $request, $entryId){
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $form = $this->createForm(UpdateBlogFormType::class, $blogPost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush($blogPost);
            return $this->redirectToRoute('homepage');
        }
        return $this->render('author/udpate_review_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //update critique admin
    /**
     * @Route("/update-critique/{entryId}", name="admin_update_all_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAllBlogPost(Request $request, $entryId){
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $form = $this->createForm(UpdateAllBlogFormType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush($blogPost);
            return $this->redirectToRoute('homepage');
        }
        return $this->render('author/update_blog_post_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //supprimer auteur
    /**
     * @Route("/delete-author/{entryId}", name="author_delete")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorAction($entryId)
    {
        $author = $this->authorRepository->findOneById($entryId);
        dump($author->findOneByUsername($this->getUser()->getUserName()));die();
        $this->entityManager->remove($author);
        $this->entityManager->flush();
        $request->getSession()->set('user_is_author', true);
        $this->addFlash('success', 'L\'auteur a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }

    //update auteur
    /**
     * @Route("/auteur-update/{entryId}", name="author_update")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAuthorEntry(Request $request, $entryId){
        $author = $this->authorRepository->findOneById($entryId);
        $form = $this->createForm(UpdateAuthorFormType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush($author);
            return $this->redirectToRoute('homepage');
        }
        return $this->render('author/update_author_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}