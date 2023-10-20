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

namespace App\Adapter\Http\WebApi;

use App\Application\ModuleFinder;
use App\Application\PackagistItemCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchModuleController extends AbstractController
{
    public function __construct(
        private readonly ModuleFinder $packagistModuleFinder
    ) {
    }

    #[Route('/webapi/search', name: 'webapi_search_modules', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $searchTerms = (string) $request->request->get('search');

        /** @var PackagistItemCollection $modules */
        $modules = $this->packagistModuleFinder->searchModule($searchTerms);

        return $this->render('webapi/search/module_search_result.html.twig', [
            'modules' => $modules->getItems(),
        ]);
    }
}
