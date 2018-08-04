<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 04/08/2018
 * Time: 09:59
 */

namespace App\Manager;


use App\Entity\BlogPost;

class BlogManager
{

    public function hydrate($book){
        $blogPost = new BlogPost();
        //on passe a $data un tableau avec des clés correspondant aux attributs
        foreach ($book as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set' . ucfirst($key);
            $blogPost-> $method($value);
        }
        return $blogPost;
    }
}