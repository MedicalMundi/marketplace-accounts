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
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientCredentialsFlowTest extends WebTestCase
{
    private const OAUTH_CLIENT_ID = 'testclient';

    private const OAUTH_CLIENT_NAME = 'test_auth_code_client';

    private const OAUTH_CLIENT_SECRET = 'test_secret';

    private string $oauthClientId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauthClientId = $this->getOauthClientIdentifier();
    }

    public function testClientCredentialsAuthenticationFlow(): void
    {
        $httpClient = self::createClient();

        $this->createOAuth2ClientCredentialsClient();

        $httpClient->request('POST', '/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->oauthClientId,
            'client_secret' => self::OAUTH_CLIENT_SECRET,
        ]);

        $response = $httpClient->getResponse();
        $jsonResponse = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertResponseStatusCodeSame(200);
        self::assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertLessThanOrEqual(3600, $jsonResponse['expires_in']);
        $this->assertGreaterThan(0, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
        $this->assertArrayNotHasKey('refresh_token', $jsonResponse);
    }

    public function testClientCredentialsAuthenticationFlowWithScopes(): void
    {
        $httpClient = self::createClient();

        $this->createOAuth2ClientCredentialsClient();

        $httpClient->request('POST', '/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->oauthClientId,
            'client_secret' => self::OAUTH_CLIENT_SECRET,
            'scope' => 'email security_console:read',
        ]);

        $response = $httpClient->getResponse();
        $jsonResponse = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertResponseStatusCodeSame(200);
        self::assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertLessThanOrEqual(3600, $jsonResponse['expires_in']);
        $this->assertGreaterThan(0, $jsonResponse['expires_in']);
        $this->assertNotEmpty($jsonResponse['access_token']);
        $this->assertArrayNotHasKey('refresh_token', $jsonResponse);
    }

    public function createOAuth2ClientCredentialsClient(null|string $scopes = null): void
    {
        /** @var ClientManagerInterface $OauthClientManager */
        $OauthClientManager = self::getContainer()->get(ClientManagerInterface::class);

        $client = new Client(self::OAUTH_CLIENT_NAME, $this->oauthClientId, self::OAUTH_CLIENT_SECRET);
        $client->setGrants(new Grant('client_credentials'));
        $client->setScopes(new Scope('email'), new Scope('security_console:read'));
        $OauthClientManager->save($client);
    }

    private function getOauthClientIdentifier(): string
    {
        return self::OAUTH_CLIENT_ID . '-' . uniqid();
    }
}
