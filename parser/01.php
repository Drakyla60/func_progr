<?php

use Symfony\Component\DomCrawler\Crawler;


#######Config

$forumUrl = './viewforum.php?f=28';

#######

####### Util
/**
 * Повертає перший елемент масива
 * @param $array
 * @return false|mixed
 */
function first($array)
{
    return reset($array);
}

/**
 * Итеративно зменшує масив до єдиного значенням
 * @param callable $function
 * @param array $array
 * @param null $initial
 * @return mixed|null
 */
function reduce(callable $function, array $array, $initial = null)
{
    return array_reduce($array, $function, $initial);
}

/**
 * Нормалізація адреси $url
 * @param $url
 * @return string
 */
function normalizeUrl($url): string
{
    return 'https://yiiframework.ru/forum/' . ltrim($url, './');
}

/**
 * Парсинг сторінки за $url і кешування
 * @param $url
 * @return false|mixed|string
 */

function fileCache(callable $func, $path) {
    return  function () use ($func, $path) {
        $args = func_get_args();
        $file = $path . '/' . md5(serialize($args));
        if (file_exists($file)) {
            return unserialize(file_get_contents($file));
        } else {
            $value = call_user_func_array($func, $args);
            file_put_contents($file, serialize($value));
            return $value;
        }
    };
}

function getHtml($url)
{
    return file_get_contents($url);
}

$getContent = fileCache('getHtml', __DIR__ . '/cache');


/**
 * Допоміжний метод для парсингу адреси
 * @param $url
 * @return Crawler
 */
function crawler($url): Crawler
{
    global $getContent;
    return new Crawler($getContent(normalizeUrl($url)));
}

/**
 * Обрізання лигньої інформації з $url
 * @param $url
 * @return array|string|string[]|null
 */
function clearUrl($url)
{
    return preg_replace('#\&sid=.{32}#s', '', $url);
}

/**
 * @param $memory
 * @return string
 */
function formatUsage($memory): string
{
    return number_format($memory / 1024 / 1024, 2, '.', ' ') . ' Mb';
}

######


###### Parse

/**
 * Рахує кулькість сторінок з темами форума за $forumUrl
 * @param $forumUrl
 * @return mixed
 */
function getForumMaxPageNumber($forumUrl)
{
    return max(first(crawler($forumUrl)
        ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
        ->each(function (Crawler $link) {
            return intval($link->text());
        })), 1);
}

/**
 * Парсить теми форума за адресою $forumUrl
 * @param $forumUrl
 * @return array
 */
function getForumPages($forumUrl): array
{
    echo 'Forum pages for' . clearUrl($forumUrl) . PHP_EOL;

    return array_map(function ($number) use ($forumUrl) {
        return $forumUrl . ($number > 1 ? '&start=' . (25 * ($number - 1)) : '');
    }, range(1, getForumMaxPageNumber($forumUrl)));
}

/**
 * Парсить пости форума за адресою $forumPageUrl
 * в count зберігає к-сть коментарів
 * @param $forumPageUrl
 * @return array
 */
function getForumPageTopics($forumPageUrl): array
{
    echo 'Forum pages topics for' . clearUrl($forumPageUrl) . PHP_EOL;

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

/**
 * Функція для паралельного парсингу
 * На windows там якісь проблеми і я її запустити не можу
 * @param callable $func
 * @param array $items
 * @return array|void
 */
function parallel_map(callable $func, array $items) {
    $childPids = [];
    $result = [];
    foreach ($items as $i => $item) {
        $newPid = pcntl_fork();
        if ($newPid == -1) {
            die('Can\'t fork process');
        } elseif ($newPid) {
            $childPids[] = $newPid;
            if ($i == count($items) - 1) {

                foreach ($childPids as $childPid) {
                    pcntl_waitpid($childPid, $status);
                    $sharedId = shmop_open($childPid, 'a',0, 0);
                    $shareData = shmop_read($sharedId, 0, shmop_size($sharedId));
                    $result[] = unserialize($shareData);
                    shmop_delete($sharedId);
                    shmop_close($sharedId);
                }
            }
        } else {
            $myPid = getmypid();
            echo 'Start ' . $myPid . PHP_EOL;
            $funcResult = $func($item);
            $shareData = serialize($funcResult);
            $sharedId = shmop_open($myPid, 'c', 0644, strlen($shareData));
            shmop_write($sharedId, $shareData, 0);
            echo 'Done ' . $myPid . ' ' . formatUsage(memory_get_peak_usage()) . PHP_EOL;
//            exit(0);
            posix_kill(getmypid(), SIGKILL);
        }
    }
    return $result;
}

#######
$topics = reduce('array_merge',
    array_map('getForumPageTopics',
//    parallel_map('getForumPageTopics',
        getForumPages($forumUrl)), []);

echo 'Done ' . formatUsage(memory_get_peak_usage()) . PHP_EOL;

echo clearUrl(print_r($topics[0], true));

echo PHP_EOL;