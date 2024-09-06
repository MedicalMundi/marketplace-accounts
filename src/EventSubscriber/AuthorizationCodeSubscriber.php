<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-accounts
 *
 * @copyright (c) 2024 MedicalMundi
 *
 * This software consists of voluntary contributions made by many individuals
 * {@link https://github.com/medicalmundi/marketplace-accounts/graphs/contributors developer} and is licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license https://github.com/MedicalMundi/marketplace-accounts/blob/main/LICENSE MIT
 */

namespace App\EventSubscriber;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AuthorizationCodeSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private string $firewallName;

    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly FirewallMapInterface $firewallMap
    ) {
        $this->firewallName = (string) $firewallMap->getFirewallConfig($requestStack->getCurrentRequest())->getName();
    }

    public function onLeagueOauth2ServerEventAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();
        $this->saveTargetPath($request->getSession(), $this->firewallName, $request->getUri());
        $response = new RedirectResponse($this->urlGenerator->generate('app_login'), 307);
        if ($user instanceof UserInterface) {
            if ($request->getSession()->get('consent_granted') !== null) {
                $event->resolveAuthorization($request->getSession()->get('consent_granted'));
                $request->getSession()->remove('consent_granted');
                return;
            }
            $response = new RedirectResponse($this->urlGenerator->generate('app_consent', $request->query->all()), 307);
        }
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'league.oauth2_server.event.authorization_request_resolve' => 'onLeagueOauth2ServerEventAuthorizationRequestResolve',
        ];
    }
}
