<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 08/08/2018
 * Time: 12:56
 */

namespace App\Controller;
use FeedIo\Factory;
use Symfony\Component\Routing\Annotation\Route;

class feedIoController
{
    /**
     * @Route("admin/feed", name="display_feed")
     * @return array
     */
    public function getRss(){
        // instance de Feed io
        $feedIo = Factory::create()->getFeedIo();

        // RSS à lire
        $url = 'https://www.lemonde.fr/livres/rss_full.xml';

        // Date de lecture
        $result = $feedIo->readSince($url, new \DateTime('-30 days'));

        $rss = array();
        // itére sur les items
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