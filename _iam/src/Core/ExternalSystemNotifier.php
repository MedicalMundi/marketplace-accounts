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

namespace IdentityAccess\Core;

use Ecotone\Messaging\Conversion\MediaType;
use Ecotone\Modelling\EventBus;
use Ramsey\Uuid\Uuid;

class ExternalSystemNotifier
{
    public function __construct(
        private readonly EventBus $eventBus
    ) {
    }

    public function notifyNewUserRegistrationWasAccepted(): void
    {
        $this->eventBus->publishWithRouting(
            'iam.new-user-registration-was-accepted',
            [
                'userId' => Uuid::uuid4()->toString(),
                'userEmail' => 'foobar@example.com',
            ],
            //MediaType::APPLICATION_JSON
        );
    }
}
