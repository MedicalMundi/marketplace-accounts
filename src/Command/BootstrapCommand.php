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

namespace App\Command;

use App\Entity\OAuth2ClientProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV1;

#[AsCommand(
    name: 'app:bootstrap',
    description: 'Bootstrap the application database',
)]
class BootstrapCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ClientManagerInterface $clientManager,
        private string $clientFqcn = Client::class
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email adddress', 'me@example.com')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password', 'password')
            ->addOption('redirect-uris', null, InputOption::VALUE_REQUIRED, 'Redirect URIs', 'http://localhost:8080/callback')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');
        $password = $input->getOption('password');

        $clientName = 'Test Client';
        $clientId = 'testclient';
        $clientSecret = 'testpass';
        $clientDescription = 'Test Client App';
        $scopes = ['profile', 'email', 'blog_read'];
        $grantTypes = ['authorization_code', 'refresh_token'];
        $redirectUris = explode(',', $input->getOption('redirect-uris'));

        // Create the user
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_SUPER_ADMIN','ROLE_OAUTH2_EMAIL']);
        $user->setUuid(new UuidV1());
        $user->setIsVerified(true);

        $this->em->persist($user);
        $this->em->flush();

        // Create the client
        $clientDemo = $this
            ->buildClientForDemo(
                $clientName,
                $clientId,
                $clientSecret,
                $redirectUris,
                $grantTypes,
                $scopes,
                $clientDescription
            );
        $this->clientManager->save($clientDemo);

        // Create Client Profile
        $oAuth2ClientProfile = new OAuth2ClientProfile();
        $oAuth2ClientProfile->setClient($clientDemo)
            ->setName($clientName)
            ->setDescription($clientDescription);
        $this->em->persist($oAuth2ClientProfile);
        $this->em->flush();

        $io->success('Bootstrap complete.');

        return Command::SUCCESS;
    }

    private function buildClientForDemo(string $name, string $identifier, string $secret, array $redirectUriStrings, array $grantStrings, array $scopeStrings, string $clientDescription): AbstractClient
    {
        /** @var AbstractClient $client */
        $client = new $this->clientFqcn($name, $identifier, $secret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce(false);

        return $client
            ->setRedirectUris(...array_map(static fn (string $redirectUri): RedirectUri => new RedirectUri($redirectUri), $redirectUriStrings))
            ->setGrants(...array_map(static fn (string $grant): Grant => new Grant($grant), $grantStrings))
            ->setScopes(...array_map(static fn (string $scope): Scope => new Scope($scope), $scopeStrings))
        ;
    }
}
