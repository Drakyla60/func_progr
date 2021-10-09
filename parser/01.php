<?php

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

function normalizeUrl($url): string
{
    return 'https://yiiframework.ru/forum/' . ltrim($url, './');
}

function getHtml($url) {
    return file_get_contents(normalizeUrl($url));
}

$forumUrl = './viewforum.php?f=28';

$html = getHtml($forumUrl);

$crawler = new Crawler($html);

print_r($crawler
    ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
    ->text());

echo PHP_EOL;