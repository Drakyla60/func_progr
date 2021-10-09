<?php

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';
#######Config

$forumUrl = './viewforum.php?f=28';

#######

####### Util
function first($array) {
    return reset($array);
}

function normalizeUrl($url): string
{
    return 'https://yiiframework.ru/forum/' . ltrim($url, './');
}

function getHtml($url)
{
    $file = __DIR__ . '/cache/' . md5($url);

    if (file_exists($file)) {
        return unserialize(file_get_contents($file));
    } else {
        $html = file_get_contents($url);
        file_put_contents($file, serialize($html));
        return $html;
    }
}

function crawler($url): Crawler
{
    return new Crawler(getHtml(normalizeUrl($url)));
}

function clearUrl($url) {
    return preg_replace('#\&sid=.{32}#s', '', $url);
}

######


###### Parse

function getForumMaxPageNumber($forumUrl)
{
    return max(first(crawler($forumUrl)
        ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
        ->each(function (Crawler $link) {
            return intval($link->text());
        })), 1);
}

function getForumPages($forumUrl): array
{
    echo 'Forum pages for' . clearUrl($forumUrl). PHP_EOL;

    return array_map(function ($number) use ($forumUrl) {
        return $forumUrl . ($number > 1 ? '&start=' . (25 * ($number - 1)) : '');
    }, range(1, getForumMaxPageNumber($forumUrl)));
}

function getForumPageTopics($forumPageUrl): array
{
    echo 'Forum pages topics for' . clearUrl($forumPageUrl). PHP_EOL;

    return crawler($forumPageUrl)
        ->filter('ul.topiclist.topics li dl')
        ->each(function (Crawler $crawler) {
            $link = $crawler->filter('div.list-inner a.topictitle');
            return [
                'title' => $link->html(),
                'url' => $link->attr('href'),
                'count' => intval($crawler->filter('dd.posts')->text()) + 1,
            ];
        });
}

#######
$forumPages = getForumPages($forumUrl);

echo clearUrl(print_r(getForumPageTopics($forumPages[0]), true));

echo PHP_EOL;