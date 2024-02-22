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

namespace App\Repository;

use App\Entity\AccessToken as AccessTokenEntity;
use League\Bundle\OAuth2ServerBundle\Repository\AccessTokenRepository as BaseAccessTokenRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(
        private readonly BaseAccessTokenRepository $baseAccessTokenRepository
    ) {
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        /** @var int|string|null $userIdentifier */
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->baseAccessTokenRepository->persistNewAccessToken($accessTokenEntity);
    }

    /**
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId): void
    {
        $this->baseAccessTokenRepository->revokeAccessToken($tokenId);
    }

    /**
     * @param string $tokenId
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        return $this->baseAccessTokenRepository->isAccessTokenRevoked($tokenId);
    }
}
