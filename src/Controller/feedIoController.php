<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 08/08/2018
 * Time: 12:56
 */

namespace App\Controller;


use App\Manager\AuthorManager;
use App\Manager\BlogManager;
use App\Manager\CommentManager;
use DateTime;
use FeedIo\Factory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class feedIoController
{
    /**
     * @Route("admin/feed", name="display_feed")
     * @return array
     */
    public function getRss(){
        // create a simple FeedIo instance
        $feedIo = Factory::create()->getFeedIo();


// read a feed
        $url = 'http://www.centrenationaldulivre.fr/fr/flux_rss/flux-1/format-ATOM1.0';

        $result = $feedIo->read($url);



// or read a feed since a certain date
        $result = $feedIo->readSince($url, new \DateTime('-30 days'));

// get title
        $feedTitle = $result->getFeed()->getLastModified();


        $feed=array();
        $rss = array();
// iterate through items
        foreach( $result->getFeed() as $item ) {
            $feed = array(
                'title' => $item->getTitle(),
                'link' => $item->getLink(),
                'description' => $item->getDescription(),
                'date'=>$item->getLastModified()
            );
            array_push($rss,$feed);

        }
        return $rss;
    }

}