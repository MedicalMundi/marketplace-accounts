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

namespace IdentityAccess\AdapterForReadingAccounts;

use App\Repository\UserRepository;
use Ecotone\Modelling\Attribute\QueryHandler;
use IdentityAccess\Core\ShowAllAccountsQuery;
use IdentityAccess\Core\ShowUnverifiedAccounts;

class AccountsDataProvider
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    #[QueryHandler]
    public function showAllAccounts(ShowAllAccountsQuery $query): array
    {
        return $this->userRepository->findAll();
    }

    #[QueryHandler]
    public function showUnverifiedAccounts(ShowUnverifiedAccounts $query): array
    {
        return $this->userRepository->findBy([
            'isVerified' => false,
        ], orderBy: [], limit: $query->getLimit(), offset: $query->getOffset());
    }
}
