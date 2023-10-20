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

class PackagistItem implements ModuleItem
{
    private function __construct(
        private string $name,
        private string $description,
        private string $url,
        private string $repository,
        private int $downloads
    ) {
    }

    public static function create(string $name, string $description, string $url, string $repository, int $downloads): self
    {
        return new self($name, $description, $url, $repository, $downloads);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function getDownloads(): int
    {
        return $this->downloads;
    }
}
