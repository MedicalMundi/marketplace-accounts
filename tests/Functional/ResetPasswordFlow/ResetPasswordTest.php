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

namespace App\Tests\Functional\ResetPasswordFlow;

use App\Controller\ResetPasswordController;
use App\Entity\User;
use App\Tests\Fixture\Factory\UserFactory;
use App\Tests\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ResetPasswordController::class)]
class ResetPasswordTest extends FunctionalTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function resetPageIsAccessible(): void
    {
        $this->client->request('GET', '/reset-password');

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function unknownEmailAddressAreRedirectWithSuccessMessge(): void
    {
        $unknownEmail = 'unknow@unknow.com';
        $this->client->followRedirects();
        $this->client->request('GET', '/reset-password');

        $this->client->submitForm('Send password reset email', [
            'reset_password_request_form[email]' => $unknownEmail,
        ]);

        self::assertStringContainsString(
            'If an account matching your email exists, then an email was just sent that contains a link',
            $this->lastResponseBody()
        );
    }

    #[Test]
    public function sendEmailWithLink(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne()->object();
        $this->client->request('GET', '/reset-password');

        $this->client->submitForm('Send password reset email', [
            'reset_password_request_form[email]' => $user->getEmail(),
        ]);

        $this->assertEmailCount(1); // use assertQueuedEmailCount() when using Messenger

        $email = $this->getMailerMessage();

        $this->assertEmailHtmlBodyContains($email, 'To reset your password, please visit the following link');
    }
}
