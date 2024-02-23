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

namespace App\DataFixtures\Dev;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV4;

class AdminUserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = (new User())
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setUuid(new UuidV4())
            ->setIsVerified(true);

        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'));

        $manager->persist($adminUser);

        $manager->flush();

        $superAdminUser = (new User())
            ->setEmail('superadmin@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setUuid(new UuidV4())
            ->setIsVerified(true);

        $superAdminUser->setPassword($this->passwordHasher->hashPassword($superAdminUser, 'superadmin'));

        $manager->persist($superAdminUser);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev', 'dev-admin'];
    }
}
