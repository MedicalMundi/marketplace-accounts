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

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer): Response
    {
        $isUserRegistrationEnabled = $this->getParameter('setting.is_user_registration_enabled');

        if (false === $isUserRegistrationEnabled) {
            $this->addFlash('info', 'Sorry!! Registration are disabled at the moment! Try later..');

            return $this->redirectToRoute('app_index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    (string) $form->get('plainPassword')->getData()
                )
            );

            $user->setUuid(new UuidV4());

            $entityManager->persist($user);
            $entityManager->flush();

            // deprecated
            //            $signatureComponent = $verifyEmailHelper->generateSignature(
            //                'app_verify_email',
            //                (string) $user->getId(),
            //                $user->getEmail(),
            //                [
            //                    'id' => (string) $user->getId(),
            //                ]
            //            );

            // deprecated
            //$this->sendConfirmationEmail($mailer, $signatureComponent->getSignedUrl(), $user->getEmail());

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('mailer@openemrmarketplace.com', 'OpenemrMarketplaceMailBot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('info', 'Please check your mail and confirm your email address!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    //  deprecated
    //    #[Route('/verify', name: 'app_verify_email')]
    //    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    //    {
    //        $user = $userRepository->find($request->query->get('id'));
    //
    //        if (! $user) {
    //            throw $this->createNotFoundException();
    //        }
    //
    //        try {
    //            $verifyEmailHelper->validateEmailConfirmation(
    //                $request->getUri(),
    //                (string) $user->getId(),
    //                $user->getEmail()
    //            );
    //        } catch (VerifyEmailExceptionInterface $e) {
    //            $this->addFlash('error', $e->getReason());
    //
    //            return $this->redirectToRoute('app_register');
    //        }
    //
    //        $user->setIsVerified(true);
    //
    //        $entityManager->flush();
    //
    //        $this->addFlash('success', 'Account verified! You can now login.');
    //
    //        return $this->redirectToRoute('app_login');
    //    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(Request $request): Response
    {
        //TODO: add form with email field
        return $this->render('registration/resend_verify_email.html.twig');
    }

    private function sendConfirmationEmail(MailerInterface $mailer, string $signedUrl, string $userEmail): void
    {
        $email = (new Email())
            ->from('system@auth.openemrmarketplace.com')
            ->to($userEmail)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Auth openemrmarketplace.com: please confirm your registration')
            ->text('Confirm your email at: ' . $signedUrl)
            ->html('<p> Confirm your email at: ' . $signedUrl . '</p>');

        $mailer->send($email);
    }
}
