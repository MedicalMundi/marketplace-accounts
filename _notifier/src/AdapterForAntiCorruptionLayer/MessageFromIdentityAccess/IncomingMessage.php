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

namespace Notifier\AdapterForAntiCorruptionLayer\MessageFromIdentityAccess;

use Ecotone\Messaging\Attribute\Parameter\Payload;
use Ecotone\Modelling\Attribute\EventHandler;
use Psr\Log\LoggerInterface;

class IncomingMessage
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    #[EventHandler(listenTo: 'iam.new-user-registration-was-accepted')]
    public function xxx(#[payload] array $event): void
    {
        //check idempotency

        // create new UserRecipient
        // call domain command createUserRecipient
        $this->logger->info((string) $event['userId']);
        $this->logger->info((string) $event['userEmail']);
    }
}
