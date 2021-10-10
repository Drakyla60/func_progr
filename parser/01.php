<?php

use Symfony\Component\DomCrawler\Crawler;

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
 * @param $baseUrl
 * @return Closure
 */
function createNormalizeUrl($baseUrl): Closure
{
    return function ($url) use ($baseUrl) {
        return $baseUrl . ltrim($url, './');
    };
}

/**
 *  Записує в кеш то шо прийшло в анонімні функції
 * @param callable $func
 * @param $path
 * @return Closure
 */
function fileCache(callable $func, $path): Closure
{
    return function () use ($func, $path) {
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

/**
 * Парсинг сторінки за $url
 * @param $url
 * @return false|string
 */
function getHtml($url)
{
    return file_get_contents($url);
}

/**
 * Допоміжний метод для парсингу адреси
 * @param callable $getContent
 * @param callable $normalizeUrl
 * @return Closure
 */
function createCrawler(callable $getContent, callable $normalizeUrl): Closure
{
    return function ($url) use ($getContent, $normalizeUrl) {
        return new Crawler($getContent($normalizeUrl($url)));
    };
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
 * Функція для виводу використаної Оперативної памяті
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
 * @param callable $crawler
 * @return Closure
 */
function createGetForumMaxPageNumber(callable $crawler): Closure
{
    return function ($url) use ($crawler) {
        return max(
            first($crawler($url)
                ->filter('div.action-bar.bar-top .pagination li:nth-last-of-type(2)')
                ->each(function (Crawler $link) {
                    return intval($link->text());
                })), 1);
    };
}


/**
 * Парсить теми форума за адресою $forumUrl
 * @param callable $getForumMaxPageNumber
 * @param $perPage
 * @return Closure
 */
function createGetForumPages(callable $getForumMaxPageNumber, $perPage): Closure
{
    return function ($forumUrl) use ($getForumMaxPageNumber, $perPage) {
        echo 'Forum pages for' . clearUrl($forumUrl) . PHP_EOL;

        return array_map(function ($number) use ($forumUrl, $perPage) {
            return $forumUrl . ($number > 1 ? '&start=' . ($perPage * ($number - 1)) : '');
        }, range(1, $getForumMaxPageNumber($forumUrl)));
    };
}


/**
 * Парсить пости форума за адресою $forumPageUrl
 * в count зберігає к-сть коментарів
 * @param callable $crawler
 * @return Closure
 */
function createGetForumPageTopics(callable $crawler): Closure
{
    return function ($forumPageUrl) use ($crawler) {
        echo 'Forum pages topics for' . clearUrl($forumPageUrl) . PHP_EOL;
        return $crawler($forumPageUrl)
            ->filter('ul.topiclist.topics li dl')
            ->each(function (Crawler $crawler) {
                $link = $crawler->filter('div.list-inner a.topictitle');
                return [
                    'title' => $link->html(),
                    'url' => $link->attr('href'),
                    'count' => intval($crawler->filter('dd.posts')->text()) + 1,
                ];
            });
    };
}

function createGetTopicPages($perPage): Closure
{
    return function ($topic) use ($perPage) {
        echo 'Forum pages for' . clearUrl($topic['url']) . PHP_EOL;
        return array_map(function ($number) use ($topic, $perPage) {
            return $topic['url'] . ($number > 1 ? '&start=' . ($perPage * ($number - 1)) : '');
        }, range(1, intval(($topic['count'] - 1) / $perPage) + 1));
    };
}

/**
 * @param callable $crawler
 * @return Closure
 */
function createGetTopicPageProfiles(callable $crawler): Closure
{
    return function ($topicPageUrl) use ($crawler) {
        echo 'Topic profiles for ' . clearUrl($topicPageUrl) . PHP_EOL;
        return $crawler($topicPageUrl)
            ->filter('dl.postprofile')
            ->each(function (Crawler $profile) {
                return [
                    'username' => $profile->filter('dt a.username, dt a.username-coloured')->text(),
                    'total' => $profile->filter('dd.profile-posts a')->text(),
                ];
            });
    };
}

/**
 * Функція для паралельного парсингу
 * На windows там якісь проблеми і я її запустити не можу
 * @param callable $func
 * @param array $items
 * @return array|void
 */
function parallel_map(callable $func, array $items)
{
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
                    $sharedId = shmop_open($childPid, 'a', 0, 0);
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


function squashProfiles(array $total, array $current) {
    $existsFilter = function ($item) use ($current) {
        return $item['username'] === $current['username'];
    };
    $notExistsFilter = function ($item) use ($current) {
        return $item['username'] !== $current['username'];
    };
    $increase = function ($exists) {
        return [
            'username' => $exists['username'],
            'count' => $exists['count'] + 1,
            'total' => $exists['total'],
        ];
    };
    $create = function ($current) {
        return [
            'username' => $current['username'],
            'count' => 1,
            'total' => $current['total'],
        ];
    };
    if ($exists = first(array_filter($total, $existsFilter))) {
        return array_merge(array_filter($total, $notExistsFilter), [$increase($exists)]);
    } else {
        return array_merge($total, [$create($current)]);
    }
}

#######Config
$normalizeUrl = createNormalizeUrl('https://yiiframework.ru/forum/');
$forumUrl = './viewforum.php?f=28';
$getContent = fileCache('getHtml', __DIR__ . '/cache');
#######


####### Logic
$crawler = createCrawler($getContent, $normalizeUrl);
$getForumMaxPageNumber = createGetForumMaxPageNumber($crawler);
$getForumPages = createGetForumPages($getForumMaxPageNumber, 25);
$getForumPageTopics = createGetForumPageTopics($crawler);
$getTopicPages = createGetTopicPages(20);
$getTopicPageProfiles = createGetTopicPageProfiles($crawler);

$topics =
    reduce('squashProfiles',
    reduce('array_merge',
        array_map($getTopicPageProfiles,
            reduce('array_merge',
                array_map($getTopicPages,
                    reduce('array_merge',
                        array_map($getForumPageTopics,
                            $getForumPages($forumUrl)), [])), [])), []), []);

#######

echo 'Done ' . formatUsage(memory_get_peak_usage()) . PHP_EOL;

echo clearUrl(print_r($topics, true));

echo PHP_EOL;