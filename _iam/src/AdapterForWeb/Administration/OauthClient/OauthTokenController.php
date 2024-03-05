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

namespace IdentityAccess\AdapterForWeb\Administration\OauthClient;

use Ecotone\Modelling\QueryBus;
use IdentityAccess\Core\ShowAllOauthAccessTokenQuery;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\RefreshTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/oauth/token')]
class OauthTokenController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {
    }

    #[Route('/', name: 'iam_admin_oauth_token_index', methods: 'GET')]
    public function showAllOauthAccessToken(): Response
    {
        $oauthTokens = (array) $this->queryBus->send(new ShowAllOauthAccessTokenQuery());

        return $this->render('@iam/administration/oauth/tokens/index.html.twig', [
            'oauthTokens' => $oauthTokens,
        ]);
    }

    #[Route('/clear/expired/access-tokens', name: 'iam_admin_oauth_token_clear_expired_access_token', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN')]
    public function clearExpiredAccessTokens(AccessTokenManagerInterface $accessTokenManager): Response
    {
        $numOfClearedAccessTokens = $accessTokenManager->clearExpired();

        if (0 === $numOfClearedAccessTokens) {
            $this->addFlash('info', 'There are not expired access tokens');
        } else {
            $this->addFlash('success', sprintf(
                'Cleared %d expired access token%s.',
                $numOfClearedAccessTokens,
                1 === $numOfClearedAccessTokens ? '' : 's'
            ));
        }

        return $this->redirectToRoute('iam_admin_oauth_token_index');
    }

    #[Route('/clear/expired/refresh-tokens', name: 'iam_admin_oauth_token_clear_expired_refresh_token', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN')]
    public function clearExpiredRefreshTokens(RefreshTokenManagerInterface $refreshTokenManager): Response
    {
        $numOfClearedRefreshTokens = $refreshTokenManager->clearExpired();

        if (0 === $numOfClearedRefreshTokens) {
            $this->addFlash('info', 'There are not expired refresh tokens');
        } else {
            $this->addFlash('success', sprintf(
                'Cleared %d expired refresh token%s.',
                $numOfClearedRefreshTokens,
                1 === $numOfClearedRefreshTokens ? '' : 's'
            ));
        }

        return $this->redirectToRoute('iam_admin_oauth_token_index');
    }

    #[Route('/clear/expired/auth-codes', name: 'iam_admin_oauth_token_clear_expired_auth_codes', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN')]
    public function clearExpiredAuthCodes(AuthorizationCodeManagerInterface $authorizationCodeManager): Response
    {
        $numOfClearedAuthCodes = $authorizationCodeManager->clearExpired();

        if (0 === $numOfClearedAuthCodes) {
            $this->addFlash('info', 'There are not expired auth codes');
        } else {
            $this->addFlash('success', sprintf(
                'Cleared %d expired auth code%s.',
                $numOfClearedAuthCodes,
                1 === $numOfClearedAuthCodes ? '' : 's'
            ));
        }

        return $this->redirectToRoute('iam_admin_oauth_token_index');
    }
}
