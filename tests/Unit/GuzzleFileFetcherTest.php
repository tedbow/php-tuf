<?php

namespace Tuf\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tuf\Client\GuzzleFileFetcher;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\RepoFileNotFound;
use Tuf\Tests\TestHelpers\UtilsTrait;

/**
 * @coversDefaultClass \Tuf\Client\GuzzleFileFetcher
 */
class GuzzleFileFetcherTest extends TestCase
{
    use UtilsTrait;
    
    /**
     * Data provider for testFetchFile().
     *
     * @return array[]
     *   Sets of arguments to pass to the test method.
     */
    public function dataProvider(): array
    {
        return static::getKeyedArray([
            [404, 256, RepoFileNotFound::class],
            [403, 256, 'RuntimeException'],
            [500, 256, 'RuntimeException'],
            [200, 4, DownloadSizeException::class],
            [200, 256, null],
        ]);
    }

    /**
     * Tests various conditions when fetching a file.
     *
     * @param integer $statusCode
     *   The response status code.
     * @param integer $maxBytes
     *   The maximum number of bytes to read from the response.
     * @param string|null $exceptionClass
     *   The expected exception class that will be thrown, if we expect the file
     *   fetcher to throw an exception.
     *
     * @return void
     *
     * @dataProvider dataProvider
     *
     * @covers ::fetchFile
     */
    public function testFetchFile(int $statusCode, int $maxBytes, ?string $exceptionClass): void
    {
        $content = json_encode(['oolong', 'assam', 'matcha', 'herbal']);
        $mockHandler = new MockHandler();
        $mockHandler->append(new Response($statusCode, [], $content));
        $handlerStack = HandlerStack::create($mockHandler);
        // The httpErrors() middleware will throw an exception if the status
        // code is not 200.
        $handlerStack->push(Middleware::httpErrors());
        $history = [];
        $handlerStack->push(Middleware::history($history));
        $client = new Client(['handler' => $handlerStack]);

        $fetcher = GuzzleFileFetcher::createFromUri('https://example.com');
        $property = new \ReflectionProperty(GuzzleFileFetcher::class, 'client');
        $property->setAccessible(true);
        $property->setValue($fetcher, $client);
        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }
        $data = $fetcher->fetchFile('test.json', $maxBytes);
        $this->assertSame($content, $data);
    }

    /**
     * Tests creating a file fetcher with a repo base URI.
     *
     * @return void
     *
     * @covers ::createFromUri
     */
    public function testCreateFromUri(): void
    {
        GuzzleFileFetcher::createFromUri('https://example.com');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Repo base URI must be HTTPS: http://example.com');
        GuzzleFileFetcher::createFromUri('http://example.com');
    }
}
