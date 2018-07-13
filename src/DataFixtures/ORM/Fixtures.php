<?php

namespace App\DataFixtures\ORM;

use App\Entity\Author;
use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $author = new Author();
        $author
            ->setName('Damien')
            ->setSurname('Peyrat')
            ->setUsername('dan-nurton7')
            ->setEmail('da.test@test.fr')
            ->setShortBio(' Test fixture');
        $manager->persist($author);

        $blogPost = new BlogPost();
        $blogPost
            ->setTitle('Livre exemple')
            ->setSlug('first-post')
            ->setDescription('synopsis du livre')
            ->setcover('http://books.google.com/books/content?id=vzQbDQAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api')
            ->setReview('Avis du livre')
            ->setAuthor($author);
        $manager->persist($blogPost);
        $manager->flush();
    }
}
