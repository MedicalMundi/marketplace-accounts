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

namespace Notifier\AdapterForSendingEmail;

use Notifier\Core\CouldNotSendEmail;
use Notifier\Core\ForSendingEmail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class AdapterForSendingEmail implements ForSendingEmail
{
    private const REGISTRATION_VERIFICATION_MESSAGE_SUBJECT = 'verify your email address';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $systemEmailAddress,
        private readonly string $systemEmailName,
    ) {
    }

    public function notifyRegistrationVerificationMessageToUser(string $email, string $registrationSignedUrl, string $template = 'default'): TemplatedEmail
    {
        $userAddress = new Address($email);

        $systemAddress = new Address($this->systemEmailAddress, $this->systemEmailName);

        $email = (new TemplatedEmail())
            ->from($systemAddress)
            ->to($userAddress)
            // TODO: configure a replyTo
            //->replyTo($visitorAddress)
            ->subject(
                sprintf('%s: ' . self::REGISTRATION_VERIFICATION_MESSAGE_SUBJECT, $this->systemEmailName)
            )
            ->text($this->formatMessage($registrationSignedUrl))
            // TODO: add HTML Template
            //->textTemplate('@mailer/for-oberdan/contact_message_from_visitor.txt.twig')
            //->context([
            //'registration_signed_url' => $registrationSignedUrl,
            //])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw CouldNotSendEmail::withMessage($e->getMessage());
        }

        return $email;
    }

    private function formatMessage(string $registrationSignedUrl): string
    {
        $messageText = "Hi!";
        $messageText .= "\n";
        $messageText .= 'Please follow this link to complete your registration.';
        $messageText .= "\n";
        $messageText .= $registrationSignedUrl;
        $messageText .= "\n";
        $messageText .= "Thank you!";
        $messageText .= "\n";

        return $messageText;
    }
}
