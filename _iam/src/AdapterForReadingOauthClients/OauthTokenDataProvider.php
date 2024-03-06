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

namespace IdentityAccess\AdapterForReadingOauthClients;

use Doctrine\DBAL\Connection;
use Ecotone\Modelling\Attribute\QueryHandler;
use IdentityAccess\Core\ShowAllOauthAccessTokenQuery;

class OauthTokenDataProvider
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    #[QueryHandler]
    public function showAllClients(ShowAllOauthAccessTokenQuery $query): array
    {
        try {
            $result = $this->connection->executeQuery(
                <<<SQL
    SELECT * FROM oauth2_access_token
SQL
            )->fetchAllAssociative();
            // TODO: remove exception
        } catch (\Exception) {
            return [];
        }
        return $result;
    }
}
