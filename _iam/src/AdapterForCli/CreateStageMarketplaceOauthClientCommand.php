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

namespace IdentityAccess\AdapterForCli;

use App\Entity\OAuth2ClientProfile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:oauth:create-for-stage-marketplace',
    description: 'Create oAuth client for stage.marketplace website',
)]
class CreateStageMarketplaceOauthClientCommand extends Command
{
    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->checkDefaultOauthClientOrCreate($io);
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function createOauthClientForMarketplaceEngine()
    {
        $clientName = 'Stage Marketplace Engine Client';
        $clientId = 'stage-marketplace-engine';
        $clientSecret = 'stage-marketplace';
        $clientDescription = 'Stage Marketplace website';
        $scopes = ['email'];
        $grantTypes = ['authorization_code', 'refresh_token'];
        $redirectUris = ['https://stage.marketplace.oe-modules.com/connect/oemodules/check'];

        $oAuthClient = $this
            ->buildOauthClient(
                $clientName,
                $clientId,
                $clientSecret,
                $redirectUris,
                $grantTypes,
                $scopes,
                $clientDescription
            );

        $this->clientManager->save($oAuthClient);

        // Create Client Profile
        $oAuth2ClientProfile = new OAuth2ClientProfile();
        $oAuth2ClientProfile->setClient($oAuthClient)
            ->setName($clientName)
            ->setDescription($clientDescription);
        $this->em->persist($oAuth2ClientProfile);
        $this->em->flush();
    }

    private function buildOauthClient(string $name, string $identifier, string $secret, array $redirectUriStrings, array $grantStrings, array $scopeStrings, string $clientDescription): AbstractClient
    {
        $client = new Client($name, $identifier, $secret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce(false);

        return $client
            ->setRedirectUris(...array_map(static fn (string $redirectUri): RedirectUri => new RedirectUri($redirectUri), $redirectUriStrings))
            ->setGrants(...array_map(static fn (string $grant): Grant => new Grant($grant), $grantStrings))
            ->setScopes(...array_map(static fn (string $scope): Scope => new Scope($scope), $scopeStrings))
        ;
    }

    private function checkDefaultOauthClientOrCreate(SymfonyStyle $io): void
    {
        if (null === $this->clientManager->find('stage-marketplace-engine')) {
            $this->createOauthClientForMarketplaceEngine();
            $io->success('Oauth Client with identifier \'stage-marketplace-engine\' was created');
        }
    }
}
