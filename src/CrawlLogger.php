<?php

namespace Spatie\HttpStatusCheck;

use Spatie\Crawler\CrawlObserver;
use Spatie\Crawler\Url;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlLogger implements CrawlObserver
{
    const UNRESPONSIVE_HOST = 'Host did not respond';

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $crawledUrls = [];

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Called when the crawl will crawl the url.
     *
     * @param \Spatie\Crawler\Url $url
     */
    public function willCrawl(Url $url)
    {
    }

    /**
     * Called when the crawl will crawl has crawled the given url.
     *
     * @param \Spatie\Crawler\Url                      $url
     * @param \Psr\Http\Message\ResponseInterface|null $response
     */
    public function hasBeenCrawled(Url $url, $response)
    {
        $statusCode = $response ? $response->getStatusCode() : self::UNRESPONSIVE_HOST;

        $reason = $response ? $response->getReasonPhrase() : '';

        $colorTag = $this->getColorTagForStatusCode($statusCode);

        $timestamp = date('Y-m-d H:i:s');

        $this->output->writeln("<{$colorTag}>[{$timestamp}] {$statusCode} {$reason} - {$url}</{$colorTag}>");

        $this->crawledUrls[$statusCode][] = $url;
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
        $this->output->writeln('');
        $this->output->writeln('Crawling summary');
        $this->output->writeln('----------------');

        ksort($this->crawledUrls);

        foreach ($this->crawledUrls as $statusCode => $urls) {
            $colorTag = $this->getColorTagForStatusCode($statusCode);

            $count = count($urls);

            if (is_numeric($statusCode)) {
                $this->output->writeln("<{$colorTag}>Crawled {$count} url(s) with statuscode {$statusCode}</{$colorTag}>");
            }

            if ($statusCode == static::UNRESPONSIVE_HOST) {
                $this->output->writeln("<{$colorTag}>{$count} url(s) did have unresponsive host(s)</{$colorTag}>");
            }
        }

        $this->output->writeln('');
    }

    /**
     * Get the color tag for the given status code.
     *
     * @param string $code
     *
     * @return string
     */
    protected function getColorTagForStatusCode($code)
    {
        if (starts_with($code, '2')) {
            return 'info';
        }

        if (starts_with($code, '3')) {
            return 'comment';
        }

        return 'error';
    }
}
