<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-engine
 *
 * @copyright (c) 2023 MedicalMundi
 *
 * This software consists of voluntary contributions made by many individuals
 * {@link https://github.com/medicalmundi/marketplace-accounts/graphs/contributors developer} and is licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license https://github.com/MedicalMundi/marketplace-accounts/blob/main/LICENSE MIT
 */

namespace App\Tests\Unit\Adapter\PackagistModuleFinder;

use App\Adapter\PackagistModuleFinder\PackagistModuleFinder;
use Exception;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class PackagistModuleFinderTest extends TestCase
{
    use PackagistHttpResponseTrait;

    /**
     * @test
     */
    public function can_find_one_module(): void
    {
        $client = $this->createHttpClientWithDefaultResponse($this->packagistDefaultSingleResultResponseContent());
        $packagistModuleFinder = new PackagistModuleFinder($client);

        $foundedModulesCollection = $packagistModuleFinder->searchModule();

        self::assertEquals(1, $foundedModulesCollection->count());
    }

    /**
     * @test
     */
    public function can_find_multiple_module(): void
    {
        $client = $this->createHttpClientWithDefaultResponse($this->packagistDefaultMultipleResultResponseContent());
        $packagistModuleFinder = new PackagistModuleFinder($client);

        $foundedModulesCollection = $packagistModuleFinder->searchModule();

        self::assertEquals(2, $foundedModulesCollection->count());
    }

    /**
     * @test
     */
    public function can_handle_http_exception(): void
    {
        $this->expectException(\RuntimeException::class);

        $clientException = new \RuntimeException('Whoops! The server is down.');
        $client = $this->createHttpClientWithExceptionResponse($clientException, 500);
        $packagistModuleFinder = new PackagistModuleFinder($client);

        $packagistModuleFinder->searchModule();
    }

    /**
     * This is an edge case, the module system is based on "openemr/oe-module-installer-plugin"
     * it should always appear in the search results.
     * 0 result should never happen.
     *
     * @test
     */
    public function can_handle_a_empty_respose_content(): void
    {
        $client = $this->createHttpClientWithDefaultResponse($this->packagistEmptyResponseContent());
        $packagistModuleFinder = new PackagistModuleFinder($client);

        $foundedModulesCollection = $packagistModuleFinder->searchModule();

        self::assertEquals(0, $foundedModulesCollection->count());
    }

    /**
     * @test
     */
    public function can_generate_an_valid_http_endpoint_without_an_input_queryString_parameter(): void
    {
        $expectedEndpoint = 'https://packagist.org/search.json?q=&type=openemr-module';

        $packagistModuleFinder = new PackagistModuleFinder($client = new Client());

        $endpoint = $packagistModuleFinder->endpoint();

        self::assertStringContainsString('type=openemr-module', $endpoint);
        self::assertSame($expectedEndpoint, $endpoint);
        self::assertTrue((bool) filter_var($endpoint, FILTER_VALIDATE_URL));
    }

    /**
     * @test
     */
    public function can_generate_an_valid_http_endpoint_from_an_input_queryString_parameter(): void
    {
        $queryString = 'vendor_1';

        $expectedEndpoint = 'https://packagist.org/search.json?q=' . $queryString . '&type=openemr-module';

        $packagistModuleFinder = new PackagistModuleFinder($client = new Client());

        $endpoint = $packagistModuleFinder->endpoint($queryString);

        self::assertStringContainsString('type=openemr-module', $endpoint);
        self::assertSame($expectedEndpoint, $endpoint);
        self::assertTrue((bool) filter_var($endpoint, FILTER_VALIDATE_URL));
    }

    private function createHttpClientWithDefaultResponse(string $contentResponse, int $httpStatusCode = 200): Client
    {
        $client = new Client();

        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->expects(self::any())->method('getContents')->willReturn($contentResponse);

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->expects(self::any())->method('getStatusCode')->willReturn($httpStatusCode);
        $response->expects(self::any())->method('getBody')->willReturn($stream);

        $client->setDefaultResponse($response);

        return $client;
    }

    private function createHttpClientWithExceptionResponse(Exception $exception, int $httpStatusCode): Client
    {
        $client = new Client();
        $client->addException($exception);

        return $client;
    }

    /**
     * @test
     */
    public function httpClientTest(): void
    {
        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn($this->packagistDefaultSingleResultResponseContent());
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $client = $this->createHttpClientWithDefaultResponse($this->packagistDefaultSingleResultResponseContent());

        $returnedResponse = $client->sendRequest($this->createMock(\Psr\Http\Message\RequestInterface::class));

        $this->assertEquals($response, $returnedResponse);
        $this->assertEquals(200, $returnedResponse->getStatusCode());
    }
}
