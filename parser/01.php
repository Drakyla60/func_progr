<?php

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';
#######Config

$forumUrl = './viewforum.php?f=28';

#######

function first($array) {
    return reset($array);
}

function normalizeUrl($url): string
{
    return 'https://yiiframework.ru/forum/' . ltrim($url, './');
}

function getHtml($url)
{
    return file_get_contents(normalizeUrl($url));
}

function clearUrl($url) {
    return preg_replace('#\&sid=.{32}#s', '', $url);
}

function getForumMaxPageNumber($forumUrl) {

    $html = getHtml($forumUrl);
    $crawler = new Crawler($html);

    $page = $crawler
        ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
        ->each(function (Crawler $link) {
            return intval($link->text());
        });

    return max(reset($page), 1);
}

function getForumPages($forumUrl): array
{
    echo 'Forum pages for' . clearUrl($forumUrl). PHP_EOL;
    $getMaxPageNumber = getForumMaxPageNumber($forumUrl);
    $result = [];
    foreach (range(1, $getMaxPageNumber) as $number) {
        $result[] = $forumUrl . ($number > 1 ? '&start=' . (25 * ($number - 1)) : '');
    }
    return $result;
}

echo clearUrl(print_r(getForumPages($forumUrl)));


//echo getForumMaxPageNumber($forumUrl);

echo PHP_EOL;