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

namespace IdentityAccess\AdapterForWeb\Administration\Account;

use Ecotone\Modelling\QueryBus;
use IdentityAccess\Core\ShowAllAccountsQuery;
use IdentityAccess\Core\ShowUnverifiedAccounts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {
    }

    #[Route('/admin/accounts', name: 'iam_admin_accounts_index')]
    public function index(): Response
    {
        $allAccounts = (array) $this->queryBus->send(new ShowAllAccountsQuery());

        return $this->render('@iam/administration/accounts/index.html.twig', [
            'allAccounts' => $allAccounts,
        ]);
    }

    #[Route('/admin/accounts/unverified', name: 'iam_admin_accounts_unverified')]
    public function unverifiedAccounts(): Response
    {
        $pendingRegistrations = (array) $this->queryBus->send(new ShowUnverifiedAccounts());

        return $this->render('@iam/administration/accounts/unverified_accounts.html.twig', [
            'pendingRegistrations' => $pendingRegistrations,
        ]);
    }
}
