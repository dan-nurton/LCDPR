<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 04/08/2018
 * Time: 13:04
 */

namespace App;


class ApiBooks
{
    const _URL = 'https://www.googleapis.com/books/v1/volumes?q=';
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;


    public function getBook($isbn){

        $url= "https://www.googleapis.com/books/v1/volumes?q=";
        $book = $url.$isbn;
        $json = file_get_contents($book);
        $json_data = json_decode($json, true);
        $bookData = array();

        if(isset($json_data['items']) && !empty($json_data['items'])){
            if(!isset( $json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail']) || empty($json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])){
                $cover ="https://vignette.wikia.nocookie.net/main-cast/images/5/5b/Sorry-image-not-available.png/revision/latest/scale-to-width-down/480?cb=20160625173435";
                $bookData ['cover'] = $cover;
            }
            else{
                $cover = $json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
                $bookData ['cover'] = $cover;
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['description']) || empty( $json_data['items'][0]['volumeInfo']['description'])){
                $description ="Pas de description disponible";
                $bookData ['description'] = $description;
            }
            else{
                $description =  $json_data['items'][0]['volumeInfo']['description'];
                $bookData ['description'] = $description;
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['title'])|| empty( $json_data['items'][0]['volumeInfo']['title'])){
                $title = "Pas de titre disponible";
                $slug = "Pas de titre disponible";
                $bookData ['title'] = $title;
                $bookData ['slug'] = $slug;
            }
            else{
                $title = $json_data['items'][0]['volumeInfo']['title'];
                $slug = str_replace(' ', '_',  $json_data['items'][0]['volumeInfo']['title']);
                $bookData ['title'] = $title;
                $bookData ['slug'] = $slug;
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['categories'])|| empty($json_data['items'][0]['volumeInfo']['categories'])){
                $category = "Catégorie non définie";
                $bookData ['category'] = $category;
            }
            else{
                $category = $json_data['items'][0]['volumeInfo']['categories'][0];
                $bookData ['category'] = $category;
            }
            if(!isset( $json_data['items'][0]['volumeInfo']['authors'])|| empty($json_data['items'][0]['volumeInfo']['authors'])){
                $writer = "Auteur non défini";
                $bookData ['writer'] = $writer;
            }
            else{
                $writer = $json_data['items'][0]['volumeInfo']['authors'][0];
                $bookData ['writer'] = $writer;
            }
        }
        else{
            $bookData=[];
            return $bookData;
        }
        return $bookData;
    }



}