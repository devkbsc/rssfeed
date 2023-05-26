<?php

namespace App\Controller;

use DOMElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\RssFeed;


class HomeController extends AbstractController
{
    /**
     * @var RssFeed
     */
    private $rssfeed;

    /**
     * @param $rssfeed
     */
    public function __construct(RssFeed $rssFeed)
    {
        $this->rssfeed = $rssFeed;
    }

    /**
     * @Route("/fetch/json", name="fetch_json")
     */
    public function fetchJsonAction()
    {
        // Replace with the actual URL of the JSON resource
        $url = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=c782db1cd730403f88a544b75dc2d7a0';
        $items = [];
        $jsonData = $this->rssfeed->getDataJson($url)['jsonData'];
        $statusCode = $this->rssfeed->getDataJson($url)['statusCode'];

        if ($jsonData === null && $statusCode !== Response::HTTP_OK) {
            return new Response('Failed to parse or fetch the JSON content.', Response::HTTP_BAD_REQUEST);
        } else {
            $dataArray = array_values($jsonData);
            $items = $dataArray[2];
        }

        return $this->render('json/index.html.twig', array('contents' => $items));
    }

    /**
     * @Route("/fetch/rss", name="fetch_rss")
     */
    public function fetchRssAction()
    {
        $requestUrl = "https://www.commitstrip.com/en/feed/";

        if($this->rssfeed->getDomainName($requestUrl) === 'commitstrip.com'){
            $images = $this->rssfeed->getDomNameImages($requestUrl);
        }else {
            $images = $this->rssfeed->getAllImages($requestUrl);
        }

        return $this->render('rss/index.html.twig', array('images' => $images));
    }
}
