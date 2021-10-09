<?php

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

function normalizeUrl($url): string
{
    return 'https://yiiframework.ru/forum/' . ltrim($url, './');
}

function getHtml($url)
{
    return file_get_contents(normalizeUrl($url));
}

$forumUrl = './viewforum.php?f=28';

$html = getHtml($forumUrl);

$crawler = new Crawler($html);

$page = $crawler
    ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
    ->each(function (Crawler $link) {
        return intval($link->text());
    });

echo max(reset($page), 1);
echo PHP_EOL;