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
use App\Entity\BlogPost;
use App\Form\EntryFormType;
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
     * @Route("/author/create", name="author_create")
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
        return $this->render('admin/create_author.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/create-entry", name="admin_create_entry")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createEntryAction(Request $request)
    {
        return $this->render('admin/entry_form.html.twig');
    }

    /**
     * @Route("/", name="admin_index")
     * @Route("/entries", name="admin_entries")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entriesAction()
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
        return $this->render('admin/entries.html.twig', [
            'blogPosts' => $blogPosts,
            'author' => $author,
            'authors' => $authors,
            'blogPostsCounts' => $blogPostsCounts
        ]);
    }
    /**
     * @Route("/delete-entry/{entryId}", name="admin_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('erreur', 'Supression impossible!');
            return $this->redirectToRoute('admin_entries');
        }
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_entries');
    }
    /**
     * @Route("/delete-all-entry/{entryId}", name="admin_delete_all_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAllEntryAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_entries');
    }
    /**
     * @Route("/update-entry/{entryId}", name="admin_update_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateEntry(Request $request, $entryId){
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $form = $this->createForm(UpdateBlogFormType::class, $blogPost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush($blogPost);
            return $this->redirectToRoute('homepage');
        }
        return $this->render('admin/udpate_review.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/update-all-entry/{entryId}", name="admin_update_all_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAllEntry(Request $request, $entryId){
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $form = $this->createForm(UpdateAllBlogFormType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush($blogPost);
            return $this->redirectToRoute('homepage');
        }
        return $this->render('admin/review.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/delete-author/{entryId}", name="author_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAuthorAction($entryId)
    {
        $author = $this->authorRepository->findOneById($entryId);
        $this->entityManager->remove($author);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le post a été effacé!');
        return $this->redirectToRoute('admin_entries');
    }

    /**
     * @Route("/author-update-entry/{entryId}", name="author_update_entry")
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
        return $this->render('admin/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}