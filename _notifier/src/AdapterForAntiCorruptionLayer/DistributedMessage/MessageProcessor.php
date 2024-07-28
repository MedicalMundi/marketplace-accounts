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

namespace Notifier\AdapterForAntiCorruptionLayer\DistributedMessage;

use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Messaging\Attribute\Parameter\Headers;
use Ecotone\Messaging\Attribute\Parameter\Payload;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\CommandBus;
use Notifier\Core\UserRecipient\CreateUserRecipient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

#[Asynchronous("notifier_distributed")]
class MessageProcessor
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CacheItemPoolInterface $cacheItemPool,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[EventHandler(listenTo: 'iam.*', endpointId: "notifier_distributed_message_processor")]
    public function handleAllExternalEvents(#[Payload] array $event, #[Headers] array $headers): void
    {
        $isAlreadyProcessed = $this->cacheItemPool->getItem('already-processed-message.' . (string) $headers['id']);
        if (! $isAlreadyProcessed->isHit()) {
            // ... item does not exist in the cache
            $this->logger->info('---------------------- precess external message ----------------------');
            //TODO:
            // check for unique Id and email before creation
            $command = new CreateUserRecipient();
            $command->id = Uuid::fromString((string) $event['userId']);
            $command->email = (string) $event['userEmail'];
            $command->isVerified = false;

            $this->commandBus->send($command);

            $isAlreadyProcessed->set([
                'processed' => true,
            ]);
            $this->cacheItemPool->save($isAlreadyProcessed);
        } else {
            $this->logger->info('---------------------- SKIPPED ALREADY PROCESSED MESSAGE ----------------------');
        }
    }
}