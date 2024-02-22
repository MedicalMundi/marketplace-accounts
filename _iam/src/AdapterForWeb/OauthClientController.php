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

namespace IdentityAccess\AdapterForWeb;

use Ecotone\Modelling\QueryBus;
use IdentityAccess\Core\GetOauthClientByIdentifierQuery;
use IdentityAccess\Core\ShowAllOauthClientQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/oauth/client')]
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

    #[Route('/{clientIdentifier}', name: 'iam_admin_oauth_client_show', methods: 'GET')]
    public function showClient(string $clientIdentifier): Response
    {
        $query = new GetOauthClientByIdentifierQuery($clientIdentifier);
        $oauthClient = (array) $this->queryBus->send($query);

        return $this->render('@iam/administration/oauth/show.html.twig', [
            'client' => $oauthClient,
        ]);
    }
}
