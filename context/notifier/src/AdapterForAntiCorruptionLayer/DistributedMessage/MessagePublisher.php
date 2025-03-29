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

namespace Notifier\AdapterForAntiCorruptionLayer\DistributedMessage;

use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\CommandBus;
use Psr\Log\LoggerInterface;

#[Asynchronous("notifier_distributed")]
class MessagePublisher
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly LoggerInterface $logger,
    ) {
    }
}
