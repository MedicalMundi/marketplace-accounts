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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:security:generate-oauth-key',
    description: 'Generates JWT keys for OAuth',
)]
class OauthGenerateKeyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the generation of new keys')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        $options = [
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        if (file_exists('config/jwt/private.pem') && ! $force) {
            $io->error('Private key already exists. Use --force to overwrite.');
            return Command::FAILURE;
        }
        if (file_exists('config/jwt/public.pem') && ! $force) {
            $io->error('Private key already exists. Use --force to overwrite.');
            return Command::FAILURE;
        }

        if (! file_exists('config/jwt')) {
            mkdir('config/jwt', 0755, true);
        }

        $key = openssl_pkey_new($options);
        if (! $key) {
            return Command::FAILURE;
        }
        openssl_pkey_export($key, $privateKey);
        $details = openssl_pkey_get_details($key);
        if (! $details) {
            return Command::FAILURE;
        }
        $publicKey = $details['key'];

        file_put_contents('config/jwt/private.pem', $privateKey);
        file_put_contents('config/jwt/public.pem', $publicKey);

        return Command::SUCCESS;
    }
}
