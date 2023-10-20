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

namespace App\Adapter\PackagistModuleFinder;

use App\Application\ModuleFinder;
use App\Application\ModuleItemCollection;
use App\Application\PackagistItem;
use App\Application\PackagistItemCollection;

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

class PackagistModuleFinder implements ModuleFinder
{
    private const HOST = 'packagist.org';

    public function __construct(
        private readonly ClientInterface $httpClient
    ) {
    }

    public function searchModule(string $queryString = ''): ModuleItemCollection
    {
        $endpoint = $this->endpoint($queryString);
        $jsonResponse = $this->doSend($endpoint);
        $decodedResponse = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        $itemsFromResponse = $decodedResponse['results'];

        return $this->buildCollection($itemsFromResponse);
    }

    public function endpoint(string $queryString = ''): string
    {
        return sprintf('https://%s/search.json?q=%s&type=openemr-module', self::HOST, $queryString);
    }

    /**
     * @throws ClientExceptionInterface
     */
    private function doSend(string $httpEndpoint): string
    {
        $response = $this->httpClient->sendRequest(
            new Request(
                'GET',
                $httpEndpoint
            )
        );

        if (200 !== $response->getStatusCode()) {
            // TODO improve exception handeling - see php-http doc.
            throw new RuntimeException('Http error.');
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param iterable<array> $itemsFromResponse
     */
    private function buildCollection(iterable $itemsFromResponse): PackagistItemCollection
    {
        $packagistItems = [];

        foreach ($itemsFromResponse as $item) {
            $packagistItems[] = PackagistItem::create((string) $item['name'], (string) $item['description'], (string) $item['url'], (string) $item['repository'], (int) $item['downloads']);
        }

        return new PackagistItemCollection($packagistItems);
    }
}
