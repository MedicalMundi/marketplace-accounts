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

namespace Notifier\Core\UserRecipient;

use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\Identifier;
use Ecotone\Modelling\WithEvents;
use Ramsey\Uuid\UuidInterface;

#[Aggregate]
class UserRecipient
{
    use WithEvents;

    public function __construct(
        #[Identifier]
        private readonly UuidInterface $id,
        private readonly string $email,
        private readonly bool $isVerified
    ) {
    }

    #[CommandHandler]
    public static function register(CreateUserRecipient $command): self
    {
        $userRecipient = new self($command->id, $command->email, $command->isVerified);
        $userRecipient->recordThat(new UserRecipientWasCreated($command->id));

        return $userRecipient;
    }
}
