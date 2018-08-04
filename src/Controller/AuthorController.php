<?php
namespace App\Controller;
use App\Form\UpdateAllBlogFormType;
use App\Form\UpdateAuthorFormType;
use App\Form\UpdateBlogFormType;
use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AuthorFormType;
/**
 * @Route("/admin")
 */
class AuthorController extends Controller
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
     * @Route("/auteur/creation", name="author_create")
     */
    public function createAuthorAction(Request $request)
    {
        if ($this->authorManager->findUser($this->getUser()->getUserName())) {
            return $this->redirectToRoute('homepage');
        }
        $author = $this->authorManager->create($this->getUser()->getUserName());
        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->authorManager->save($author);
            $request->getSession()->set('user_is_author', true);
            $this->addFlash('success', 'Félicitation! Vous pouvez maintenant poster vos critiques!');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('author/create_author.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="admin_index")
     * @Route("/panel", name="admin_panel")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function panelAction()
    {
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        $authors = [];
        $blogPosts = [];
        $blogPostsCounts = [];
        if ($author) {
            $blogPosts = $this->blogManager->findByAuthor($author);
        }
        if($author->isAdmin()){
            $blogPosts = $this->blogManager->findBlogPostForAdmin();
            $authors = $this->authorManager->findForAdmin();
            foreach($authors as $author){
                $blogPostsCounts [$author->getPseudo()]=  $this->blogManager->countByAuthor($author);
            }
        }
        return $this->render('author/panel.html.twig', [
            'blogPosts' => $blogPosts,
            'author' => $author,
            'authors' => $authors,
            'blogPostsCounts' => $blogPostsCounts
        ]);
    }

    // supprimer crtique de l'utilisateur
    /**
     * @Route("/supprimer-critique-utilisateur/{blogPostId}", name="delete_review")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorReviewAction($blogPostId)
    {
        $blogPost = $this->blogManager->find($blogPostId);
        $author = $this->authorManager->findUser($this->getUser()->getUserName());
        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('erreur', 'Supression impossible!');
            return $this->redirectToRoute('admin_panel');
        }
        $this->blogManager->remove($blogPost);
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }

    // supprimer critiques administrateur
    /**
     * @Route("/supprimer-toutes-critiques/{blogPostId}", name="admin_delete_review")
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAdminReviewsAction($blogPostId)
    {
        $blogPost = $this->blogManager->find($blogPostId);
        $this->blogManager->remove($blogPost);
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }

    //update critique utilisateur
    /**
     * @Route("/update-critique-utilisateur/{blogPostId}", name="update_review")
     * @param Request $request
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAuthorReviewAction(Request $request, $blogPostId){
        $blogPost = $this->blogManager->find($blogPostId);
        $form = $this->createForm(UpdateBlogFormType::class, $blogPost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogManager->save($blogPost);
            return $this->redirectToRoute('admin_panel');
        }
        return $this->render('author/udpate_review_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //update critique admin
    /**
     * @Route("/update-critique/{blogPostId}", name="admin_update_review")
     * @param Request $request
     * @param $blogPostId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAdminReviewsAction(Request $request, $blogPostId){
        $blogPost = $this->blogManager->find($blogPostId);
        $form = $this->createForm(UpdateAllBlogFormType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogManager->save($blogPost);
            return $this->redirectToRoute('admin_panel');
        }
        return $this->render('author/update_blog_post_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //supprimer auteur
    /**
     * @Route("/delete-author/{authorId}", name="delete_author")
     * @param $authorId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorAction($authorId)
    {
        $author =$this->authorManager->findById($authorId);
        $this->authorManager->remove($author);
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_panel');
    }

    //update auteur
    /**
     * @Route("/auteur-update/{authorId}", name="update_author")
     * @param Request $request
     * @param $authorId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAuthorAction(Request $request, $authorId){
        $author = $this->authorManager->findById($authorId);
        $form = $this->createForm(UpdateAuthorFormType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->authorManager->save($author);
            return $this->redirectToRoute('admin_panel');
        }
        return $this->render('author/update_author_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}