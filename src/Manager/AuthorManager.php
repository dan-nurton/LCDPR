<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 04/08/2018
 * Time: 17:19
 */

namespace App\Manager;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuthorManager extends Controller
{
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
    public function save($author){
        $this->entityManager->persist($author);
        $this->entityManager->flush($author);
    }

    public function remove($author){
        $this->entityManager->remove($author);
        $this->entityManager->flush();
    }

    public function create($user){
        $author = new Author();
        $author->setUsername($user);
        return $author;
    }

    public function findUser($user){
       $user = $this->authorRepository->findOneByUsername($user);
       return $user;
    }

    public function findByName($name){
       $author =  $this->authorRepository->findOneByUsername($name);
       return $author;
    }

    public function findById($id){
        $author =  $this->authorRepository->find($id);
        return $author;
    }

    public function findForAdmin(){
        $authors = $this->authorRepository->getAllAuthorsForAdmin();
        return $authors;
    }
}