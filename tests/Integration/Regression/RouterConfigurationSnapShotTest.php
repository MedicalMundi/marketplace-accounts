<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-accounts
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

namespace App\Tests\Integration\Regression;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Snapshot test about the router configuration
 *
 * When you add new route run phpunit with env var UT=1 (Update Test)
 * UT=1 bin/phpunit --filter=RouterConfigurationSnapShotTest --group regression
 */
#[CoversNothing]
#[Group('regression')]
class RouterConfigurationSnapShotTest extends KernelTestCase
{
    public function testRouterConfigurationEndpoint(): void
    {
        /** @var ContainerInterface $container */
        $container = self::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get('router');

        $routeCollection = $router->getRouteCollection();

        $routeMap = $this->createRouteMap($routeCollection);

        $currentRouteMapJson = (string) json_encode($routeMap, JSON_PRETTY_PRINT); //  Json::encode($routeMap, Json::PRETTY);

        $expectedRouteMapFile = __DIR__ . '/Fixture/expected_route_map.json';

        if ((bool) getenv('UT')) {
            (new Filesystem())->dumpFile($expectedRouteMapFile, $currentRouteMapJson);
        }

        self::assertJsonStringEqualsJsonFile(
            $expectedRouteMapFile,
            $currentRouteMapJson
        );
    }

    private function createRouteMap(RouteCollection $routeCollection): array
    {
        $routeMap = [];
        foreach ($routeCollection->all() as $name => $route) {
            $routeMap[$name] = [
                'path' => $route->getPath(),
                'requirements' => $route->getRequirements(),
                'defaults' => $route->getDefaults(),
                'methods' => $route->getMethods(),
            ];
        }

        ksort($routeMap);

        return $routeMap;
    }
}
