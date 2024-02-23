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

namespace Notifier\Tests\Integration\Regression;

use Notifier\AdapterForSendingEmail\AdapterForSendingEmail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[CoversClass(AdapterForSendingEmail::class)]
#[Group('regression')]
class AdapterForSendingEmailConfigurationSnapShotTest extends KernelTestCase
{
    #[Test]
    public function ShouldUseEmailSenderParamsDefinedInServiceContainer(): void
    {
        /** @var ContainerInterface $container */
        $container = self::getContainer();
        $expectedSystemEmailAddress = $container->getParameter('notifier.setting.recipient.system.email');
        $expectedSystemEmailName = $container->getParameter('notifier.setting.recipient.system.name');
        $adapterForSendingEmail = $container->get(AdapterForSendingEmail::class);
        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);

        $senderAddress = $sendedEmail->getFrom();
        self::assertSame($expectedSystemEmailAddress, $senderAddress[0]->getAddress());
        self::assertSame($expectedSystemEmailName, $senderAddress[0]->getName());
    }
}
