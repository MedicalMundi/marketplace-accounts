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

namespace App\Repository;

use App\Entity\OAuth2ClientProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuth2ClientProfile>
 *
 * @method OAuth2ClientProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method OAuth2ClientProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method OAuth2ClientProfile[]    findAll()
 * @method OAuth2ClientProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OAuth2ClientProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuth2ClientProfile::class);
    }

    public function add(OAuth2ClientProfile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2ClientProfile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
