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

namespace IdentityAccess\Tests\Functional\Regression;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversNothing]
class WebSecuredAreasAreProtectedTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    #[DataProvider('restrictedWebUrlDataProvider')]
    #[Test]
    public function restrictedPageIsRedirectedToLogin(string $restrictedUrl, array $urlParams = []): void
    {
        $this->client->request('GET', $this->urlTo($restrictedUrl, $urlParams));

        self::assertTrue($this->client->getResponse()->isRedirect('/login'));
    }

    public static function restrictedWebUrlDataProvider()
    {
        return [
            ['iam_admin_index'],

            ['iam_admin_accounts_index'],
            ['iam_admin_accounts_unverified'],

            ['iam_admin_oauth_client_index'],
            ['iam_admin_oauth_client_show', ['clientIdentifier' => 'fakeIdentifier']],
            ['iam_admin_oauth_client_new'],
            ['iam_admin_oauth_client_edit', ['clientIdentifier' => 'fakeIdentifier']],

            ['iam_admin_oauth_token_index'],
            ['iam_admin_oauth_token_clear_expired_access_token'],
            ['iam_admin_oauth_token_clear_expired_refresh_token'],
            ['iam_admin_oauth_token_clear_expired_auth_codes'],
        ];
    }

    /**
     * @param array<mixed> $parameters
     */
    protected function urlTo(string $path, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->container()->get('router')->generate($path, $parameters, $referenceType);
    }

    protected function container(): ContainerInterface
    {
        return self::$kernel->getContainer()->get('test.service_container');
    }
}
