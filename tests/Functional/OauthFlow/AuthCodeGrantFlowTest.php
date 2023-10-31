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

namespace App\Tests\Functional\OauthFlow;

use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthCodeGrantFlowTest extends WebTestCase
{
    private const OAUTH_CLIENT_ID = 'testclient';

    private const OAUTH_CLIENT_NAME = 'test_auth_code_client';

    private const OAUTH_CLIENT_SECRET = 'test_secret';

    private const OAUTH_CLIENT_REDIRECT_URI = 'https://localhost:3001';

    private string $oauthClientId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauthClientId = $this->getOauthClientIdentifier();
    }

    public function testAuthenticationCodeFlow(): void
    {
        self::markTestSkipped('Fail on GHA CI');
        $httpClient = self::createClient();

        $this->createOAuth2AuthCodeClient();

        $clientId = $this->oauthClientId;

        $redirectUri = self::OAUTH_CLIENT_REDIRECT_URI;

        $httpClient->request('GET', '/authorize', [
            'response_type' => 'code',
            'client_id' => $clientId,
            //'redirect_uri' => $redirectUri,
            'scope' => 'email',
            'state' => 'foobar',
        ]);

        //dd($httpClient->getRequest());
        //dd($httpClient->getResponse());
    }

    public function createOAuth2AuthCodeClient(): void
    {
        /** @var ClientManagerInterface $OauthClientManager */
        $OauthClientManager = self::getContainer()->get(ClientManagerInterface::class);

        $client = new Client(self::OAUTH_CLIENT_NAME, $this->oauthClientId, self::OAUTH_CLIENT_SECRET);
        //$client->setScopes(...array_map(fn (string $scope) => new Scope($scope), []));
        $client->setGrants(new Grant('authorization_code'), new Grant('refresh_token'));
        $client->setRedirectUris(new RedirectUri(self::OAUTH_CLIENT_REDIRECT_URI));
        $client->setScopes(new Scope('email'), new Scope('security_console:read'));

        $OauthClientManager->save($client);
    }

    private function getOauthClientIdentifier(): string
    {
        return self::OAUTH_CLIENT_ID . '-' . uniqid();
    }
}
