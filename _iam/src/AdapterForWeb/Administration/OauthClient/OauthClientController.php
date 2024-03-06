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
use IdentityAccess\AdapterForWeb\Administration\OauthClient\Form\Dto\OauthClientDto;
use IdentityAccess\AdapterForWeb\Administration\OauthClient\Form\OauthClientType;
use IdentityAccess\Core\GetOauthClientByIdentifierQuery;
use IdentityAccess\Core\ShowAllOauthClientQuery;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/oauth/client')]
#[IsGranted('ROLE_ADMIN')]
class OauthClientController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {
    }

    #[Route('/', name: 'iam_admin_oauth_client_index', methods: 'GET')]
    public function showAllOauthClients(): Response
    {
        $oauthClients = (array) $this->queryBus->send(new ShowAllOauthClientQuery());

        return $this->render('@iam/administration/oauth/index.html.twig', [
            'oauthClients' => $oauthClients,
        ]);
    }

    #[Route('/details/{clientIdentifier}', name: 'iam_admin_oauth_client_show', methods: 'GET')]
    public function showClient(string $clientIdentifier): Response
    {
        $query = new GetOauthClientByIdentifierQuery($clientIdentifier);
        $oauthClient = (array) $this->queryBus->send($query);

        return $this->render('@iam/administration/oauth/show.html.twig', [
            'client' => $oauthClient,
        ]);
    }

    #[Route('/new', name: 'iam_admin_oauth_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientManagerInterface $clientManager): Response
    {
        $oAuthClient = new OauthClientDto();
        $form = $this->createForm(
            OauthClientType::class,
            $oAuthClient,
            [
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $this->buildOauthClientFromDto($form->getData());
            $clientManager->save($client);

            return $this->redirectToRoute('iam_admin_oauth_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@iam/administration/oauth/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/{clientIdentifier}', name: 'iam_admin_oauth_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $clientIdentifier, ClientManagerInterface $clientManager): Response
    {
        if (null === $client = $clientManager->find($clientIdentifier)) {
            $this->addFlash('info', "The requested oAuth client not exist.");
            return $this->redirectToRoute('iam_admin_oauth_client_index', [], Response::HTTP_SEE_OTHER);
        }

        $clientDto = new OauthClientDto();
        $clientDto->name = $client->getName();
        $clientDto->identifier = $client->getIdentifier();
        $clientDto->identifier = $client->getIdentifier();
        $clientDto->secret = $client->getSecret();
        $clientDto->redirectUris = $client->getRedirectUris();
        $clientDto->grants = $client->getGrants();
        $clientDto->scopes = $client->getScopes();
        $clientDto->active = $client->isActive();
        $clientDto->allowPlainTextPkce = $client->isPlainTextPkceAllowed();

        $form = $this->createForm(OauthClientType::class, $clientDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var OauthClientDto $formData */
            $formData = $form->getData();

            $client->setActive($formData->active);
            $client->setAllowPlainTextPkce($formData->allowPlainTextPkce);
            $client->setRedirectUris(...array_map(static fn (string $redirectUri): RedirectUri => new RedirectUri($redirectUri), $formData->redirectUris));
            //TODO:
            //$client->setGrants($formData->grants);
            //$client->setScopes($formData->scopes);

            $clientManager->save($client);

            $this->addFlash('success', 'oAuth client(' . $client->getName() . ') updated.');

            return $this->redirectToRoute('iam_admin_oauth_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@iam/administration/oauth/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{clientIdentifier}', name: 'iam_admin_oauth_client_delete', methods: ['POST'])]
    public function delete(Request $request, string $clientIdentifier, ClientManagerInterface $clientManager): Response
    {
        if (null === $client = $clientManager->find($clientIdentifier)) {
            $this->addFlash('info', "The requested oAuth client not exist.");
            return $this->redirectToRoute('iam_admin_oauth_client_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $client->getIdentifier(), (string) $request->request->get('_token'))) {
            $clientManager->remove($client);
            $this->addFlash('success', "oAuth client (" . $client->getName() . ") was deleted.");
        }

        return $this->redirectToRoute('iam_admin_oauth_client_index', [], Response::HTTP_SEE_OTHER);
    }

    private function buildOauthClientFromDto(OauthClientDto $dto): ClientInterface
    {
        $defaultGrants = ['authorization_code', 'refresh_token'];
        $defaultScope = ['email'];

        $dto->grants = $defaultGrants;
        $dto->scopes = $defaultScope;
        $client = new Client($dto->name, $dto->identifier, $dto->secret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce(false);

        return $client
            ->setRedirectUris(...array_map(static fn (string $redirectUri): RedirectUri => new RedirectUri($redirectUri), $dto->redirectUris))
            ->setGrants(...array_map(static fn (string $grant): Grant => new Grant($grant), $dto->grants))
            ->setScopes(...array_map(static fn (string $scope): Scope => new Scope($scope), $dto->scopes))
        ;
    }
}
