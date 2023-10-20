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

use App\Application\ModuleItemCollection;
use App\Application\PackagistItem;
use App\Application\PackagistItemCollection;
use PHPUnit\Framework\TestCase;

class PackagistItemCollectionTest extends TestCase
{
    private const IRRELEVANT = 'irrelevant';

    //use PackagistHttpResponseTrait;

    /**
     * @test
     */
    public function can_be_created(): void
    {
        $collection = new PackagistItemCollection();

        self::assertInstanceOf(ModuleItemCollection::class, $collection);
        self::assertInstanceOf(PackagistItemCollection::class, $collection);
    }

    /**
     * @test
     */
    public function can_be_created_with_data(): void
    {
        $data = [
            packagistItem::create(
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                10
            ),
        ];

        $collection = new PackagistItemCollection($data);

        self::assertInstanceOf(PackagistItemCollection::class, $collection);
    }

    /**
     * @test
     */
    public function can_count_internal_elements(): void
    {
        $data = [
            packagistItem::create(
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                10
            ),
            packagistItem::create(
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                10
            ),
        ];

        $collection = new PackagistItemCollection($data);

        self::assertEquals(2, $collection->count());
    }

    /**
     * @test
     */
    public function can_return_elements_as_array(): void
    {
        $data = [
            packagistItem::create(
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                10
            ),
            packagistItem::create(
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                self::IRRELEVANT,
                10
            ),
        ];
        $collection = new PackagistItemCollection($data);

        $items = $collection->getItems();

        self::assertTrue(\is_array($items));
    }
}
