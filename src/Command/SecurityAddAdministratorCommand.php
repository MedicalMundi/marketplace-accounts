<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-engine
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

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV4;

#[AsCommand(
    name: 'app:security:add-administrator',
    description: 'Add a user account with administrator privileges',
)]
class SecurityAddAdministratorCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'User email')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $this->getEmailArgumentOrError($input, $io);

        $password = $this->getPasswordArgumentOrError($input, $io);

        $adminUser = $this->createAdminUser($email, $password);

        try {
            $this->userRepository->add($adminUser, true);
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $io->success('New Admin user was created.');
        $io->info('User login email: ' . $adminUser->getEmail());

        return Command::SUCCESS;
    }

    private function getEmailArgumentOrError(InputInterface $input, SymfonyStyle $io): string
    {
        $email = (string) $input->getArgument('email');

        if ($email === '') {
            $io->error('Empty mail');
            exit;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('Invalid email: %s', $email));
            exit;
        }

        return $email;
    }

    private function getPasswordArgumentOrError(InputInterface $input, SymfonyStyle $io): string
    {
        $password = (string) $input->getArgument('password');

        if ($password === '') {
            $io->error('Empty password');
            exit;
        }

        return $password;
    }

    private function createAdminUser(string $email, string $password): User
    {
        $adminUser = new User();
        $adminUser->setEmail($email);
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setUuid(new UuidV4());
        $adminUser->setPassword(
            $this->passwordHasher->hashPassword(
                $adminUser,
                $password
            )
        );

        return $adminUser;
    }
}
