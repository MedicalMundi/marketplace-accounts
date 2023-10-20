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

namespace App\Tests\Unit\Application;

use App\Application\ModuleItem;
use App\Application\PackagistItem;
use PHPUnit\Framework\TestCase;

class PackagistItemTest extends TestCase
{
    private const IRRELEVANT = 'irrelevant';

    /**
     * @test
     */
    public function can_be_created(): void
    {
        $packagistItem = PackagistItem::create(
            self::IRRELEVANT,
            self::IRRELEVANT,
            self::IRRELEVANT,
            self::IRRELEVANT,
            100
        );

        self::assertInstanceOf(ModuleItem::class, $packagistItem);
        self::assertInstanceOf(PackagistItem::class, $packagistItem);
        self::assertSame(self::IRRELEVANT, $packagistItem->getName());
        self::assertSame(self::IRRELEVANT, $packagistItem->getDescription());
        self::assertSame(self::IRRELEVANT, $packagistItem->getUrl());
        self::assertSame(self::IRRELEVANT, $packagistItem->getRepository());
        self::assertSame(100, $packagistItem->getDownloads());
    }
}
