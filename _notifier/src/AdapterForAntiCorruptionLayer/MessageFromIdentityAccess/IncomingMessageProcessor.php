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

use Ecotone\Messaging\Attribute\Parameter\Headers;
use Ecotone\Messaging\Attribute\Parameter\Payload;
use Ecotone\Modelling\Attribute\EventHandler;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class IncomingMessageProcessor
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CacheItemPoolInterface $cacheItemPool
    ) {
    }

    #[EventHandler(listenTo: 'iam.new-user-registration-was-accepted')]
    public function handle(#[payload] array $event, #[headers] array $headers): void
    {

        $isAlreadyProcessed = $this->cacheItemPool->getItem('already-processed-message.' . (string) $headers['id']);
        if (!$isAlreadyProcessed->isHit()) {
            // ... item does not exist in the cache
            $this->logger->info('---------------------- precess external message ----------------------');
            //TODO:
            // create new UserRecipient
            // call domain command createUserRecipient
            // or
            // create own projections from external events.

            $isAlreadyProcessed->set(['processed' => true]);
            $this->cacheItemPool->save($isAlreadyProcessed);
        }else{
            $this->logger->info('---------------------- SKIPPED ALREADY PROCESSED MESSAGE ----------------------');
        }
    }
}
