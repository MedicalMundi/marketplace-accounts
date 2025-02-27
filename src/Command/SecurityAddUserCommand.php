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
    name: 'app:security:add-user',
    description: 'Add a regular user account',
)]
class SecurityAddUserCommand extends Command
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

        $email = $this->getEmailArgumentOrFailWithMessage($input, $io);

        $password = $this->getPasswordArgumentOrFailWithMessage($input, $io);

        $adminUser = $this->createUser($email, $password);

        try {
            $this->userRepository->add($adminUser, true);
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $io->success('New user was created.');
        $io->info('User login email: ' . (string) $adminUser->getEmail());

        return Command::SUCCESS;
    }

    private function getEmailArgumentOrFailWithMessage(InputInterface $input, SymfonyStyle $io): string
    {
        $email = (string) $input->getArgument('email');

        if ($email === '') {
            $io->error('Empty mail');
            exit(Command::FAILURE);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(\sprintf('Invalid email: %s', $email));
            exit(Command::FAILURE);
        }

        return $email;
    }

    private function getPasswordArgumentOrFailWithMessage(InputInterface $input, SymfonyStyle $io): string
    {
        $password = (string) $input->getArgument('password');

        if ($password === '') {
            $io->error('Empty password');
            exit(Command::FAILURE);
        }

        return $password;
    }

    private function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setUuid(new UuidV4());
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $password
            )
        );

        return $user;
    }
}
