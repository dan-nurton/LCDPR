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

    public function getBook($isbn){
        $book = self::_URL.$isbn;
        $json = file_get_contents($book);
        $json_data = json_decode($json, true);
        $bookData = array();

        if(isset($json_data['items']) && !empty($json_data['items'])){
            if(isset($json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
                $cover = $json_data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
                $bookData ['cover'] = $cover;
            }
            if(isset($json_data['items'][0]['volumeInfo']['description'])) {
                $description = $json_data['items'][0]['volumeInfo']['description'];
                $bookData ['description'] = $description;
            }
            if(isset($json_data['items'][0]['volumeInfo']['title'])) {
                $title = $json_data['items'][0]['volumeInfo']['title'];
                $slug = str_replace(' ', '_', $json_data['items'][0]['volumeInfo']['title']);
                $bookData ['title'] = $title;
                $bookData ['slug'] = $slug;
            }
            if(isset($json_data['items'][0]['volumeInfo']['categories'][0])) {
                $category = $json_data['items'][0]['volumeInfo']['categories'][0];
                $bookData ['category'] = $category;
            }
            if(isset($json_data['items'][0]['volumeInfo']['authors'][0])) {
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