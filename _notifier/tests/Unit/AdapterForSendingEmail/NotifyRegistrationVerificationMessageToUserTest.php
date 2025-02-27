<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-accounts
 *
<<<<<<< HEAD
 * @copyright (c) 2023 MedicalMundi
=======
 * @copyright (c) 2024 MedicalMundi
>>>>>>> main
 *
 * This software consists of voluntary contributions made by many individuals
 * {@link https://github.com/medicalmundi/marketplace-accounts/graphs/contributors developer} and is licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license https://github.com/MedicalMundi/marketplace-accounts/blob/main/LICENSE MIT
 */

namespace Notifier\Tests\Unit\AdapterForSendingEmail;

use Notifier\AdapterForSendingEmail\AdapterForSendingEmail;
use Notifier\Core\CouldNotSendEmail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

#[CoversClass(AdapterForSendingEmail::class)]
class NotifyRegistrationVerificationMessageToUserTest extends TestCase
{
    private const SYSTEM_EMAIL_ADDRESS = 'system@auth.openemrmarketplace.com';

    private const SYSTEM_EMAIL_NAME = 'auth.openemrmarketplace.com';

    private const EXPECTED_EMAIL_SUBJECT = self::SYSTEM_EMAIL_NAME . ': verify your email address';

    /**
     * @var MailerInterface & MockObject
     */
    private $mailer;

    private AdapterForSendingEmail $adapterForSendingEmail;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->adapterForSendingEmail = new AdapterForSendingEmail(
            $this->mailer,
            self::SYSTEM_EMAIL_ADDRESS,
            self::SYSTEM_EMAIL_NAME
        );
    }

    #[Test]
    public function shouldNotifyRegistrationVerificationMessageToUser(): void
    {
        $this->mailer->expects(self::once())
            ->method('send');

        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $this->adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);

        self::assertSame(self::EXPECTED_EMAIL_SUBJECT, $sendedEmail->getSubject());

        $recipientAddress = $sendedEmail->getTo();
        self::assertSame('user001@example.com', $recipientAddress[0]->getAddress());
    }

    #[Test]
    public function shouldContainTheSignedUrlInTextBodyMessage(): void
    {
        $this->mailer->expects(self::once())
            ->method('send');

        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $this->adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);

        self::assertStringContainsString($signedUrl, $sendedEmail->getTextBody());
    }

    #[Test]
    public function shouldContainTheSignedUrlInHtmlBodyMessage(): void
    {
        self::markTestIncomplete('Implements HTML mail template');
        $this->mailer->expects(self::once())
            ->method('send');

        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $this->adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);

        self::assertStringContainsString($signedUrl, $sendedEmail->getHtmlBody());
    }

    #[Test]
    public function shouldHaveTheCorrectSenderData(): void
    {
        $this->mailer->expects(self::once())
            ->method('send');

        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $this->adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);

        $senderAddress = $sendedEmail->getFrom();
        self::assertSame(self::SYSTEM_EMAIL_ADDRESS, $senderAddress[0]->getAddress());
        self::assertSame(self::SYSTEM_EMAIL_NAME, $senderAddress[0]->getName());
    }

    #[Test]
    public function shouldThrowCustomException(): void
    {
        $this->expectException(CouldNotSendEmail::class);

        $this->mailer->expects(self::once())
            ->method('send')
            ->willThrowException(new TransportException());

        $signedUrl = 'https://irrelevant.signed.com';

        $sendedEmail = $this->adapterForSendingEmail->notifyRegistrationVerificationMessageToUser('user001@example.com', $signedUrl);
    }
}
