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

namespace App\Application;

class PackagistItemCollection implements ModuleItemCollection
{
    /**
     * @param iterable <PackagistItem> $items
     */
    public function __construct(
        private readonly iterable $items = []
    ) {
    }

    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @return iterable <PackagistItem>
     */
    public function getItems(): iterable
    {
        return $this->items;
    }
}
