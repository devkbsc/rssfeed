<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class RssFeed
{

    public function getDomNameImages($requestUrl)
    {
        // Fetch the RSS feed using HttpClient
        $client = HttpClient::create();
        $response = $client->request('GET', $requestUrl);
        $content = $response->getContent();

        // Parse the XML content using DomCrawler
        $crawler = new Crawler();
        $crawler->addXmlContent($content);
        $domain = $this->getDomainName($requestUrl);

        $images = $crawler->filterXPath('//item/content:encoded')
            ->each(function (Crawler $node) use ($domain) {
                if (strpos($node->text(), $domain) !== false) {
                    $crawler = new Crawler($node->text());
                    return $crawler->filter('img.size-full')->attr('src');
                }
                return null;
            });

        return array_unique($images);
    }


    public function getAllImages($requestUrl)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $requestUrl);
        $content = $response->getContent();

        $crawler = new Crawler();
        $crawler->addXmlContent($content);

        $rssImages = $crawler->filterXPath('//item/content:encoded')->each(function (Crawler $node) {
            $imgCrawler = new Crawler($node->html());
            return $imgCrawler->filter('img')->each(function (Crawler $imgNode) {
                return $imgNode->attr('src');
            });
        });

        foreach ($rssImages as $item) {
            $images[] = $item[0];
        }

        return array_unique($images);
    }

    public function getDataJson($url)
    {

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $jsonData = json_decode($content, true);

        return ['jsonData' => $jsonData, 'statusCode' => $statusCode];
    }


    public function getDomainName($url)
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl && isset($parsedUrl['host'])) {
            $host = $parsedUrl['host'];
            $host = preg_replace('/^www\./', '', $host);
            $segments = explode('.', $host);
            $numSegments = count($segments);

            if ($numSegments >= 2) {
                $domain = $segments[$numSegments - 2] . '.' . $segments[$numSegments - 1];
                return $domain;
            }
        }

        return null;
    }

}
