<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\RuleBuilders\Architecture\Architecture;
use Arkitect\Rules\Rule;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__.'/src');

    $layeredArchitectureRules = Architecture::withComponents()
        ->component('Controller')->definedBy('App\Controller\*')
        ->component('Service')->definedBy('App\Service\*')
        ->component('Repository')->definedBy('App\Repository\*')
        ->component('Entity')->definedBy('App\Entity\*')

        ->where('Controller')->mayDependOnComponents('Service', 'Entity')
        ->where('Service')->mayDependOnComponents('Repository', 'Entity')
        ->where('Repository')->mayDependOnComponents('Entity')
        ->where('Entity')->shouldNotDependOnAnyComponent()

        ->rules();

    $serviceNamingRule = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Service'))
        ->should(new HaveNameMatching('*Service'))
        ->because('we want uniform naming for services');

    $repositoryNamingRule = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Repository'))
        ->should(new HaveNameMatching('*Repository'))
        ->because('we want uniform naming for repositories');

    $config->add($classSet, $serviceNamingRule, $repositoryNamingRule, ...$layeredArchitectureRules);



    /**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *
     *      IdentityAccess Context
     *
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     */

    $identityAccessClassSet = ClassSet::fromDir(__DIR__ . '/context/iam/src');

    $allowedPhpDependencies = require_once __DIR__ . '/tools/phparkitect/PhpDependencies/allowed_always.php';
    $allowedVendorDependenciesInIdentityAccessCore = require_once __DIR__ . '/tools/phparkitect/VendorDependencies/allowed_in_iam_core.php';
    $allowedVendorDependenciesInIdentityAccessAdapters = require_once __DIR__ . '/tools/phparkitect/VendorDependencies/allowed_in_iam_adapters.php';

    $identityAccessPortAndAdapterArchitectureRules = Architecture::withComponents()
        ->component('Core')->definedBy('IdentityAccess\Core\*')
        ->component('Adapters')->definedBy('IdentityAccess\AdapterFor*')
        ->component('Infrastructure')->definedBy('IdentityAccess\Infrastructure\*')

        ->where('Infrastructure')->shouldNotDependOnAnyComponent()
        ->where('Adapters')->mayDependOnComponents('Core', 'Infrastructure')
        ->where('Core')->shouldNotDependOnAnyComponent()
        ->rules();


    $allowedDependenciesInIdentityAccessCode = array_merge($allowedPhpDependencies, $allowedVendorDependenciesInIdentityAccessCore);
    $identityAccessCoreIsolationRule = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('IdentityAccess\Core'))
        ->should(new NotHaveDependencyOutsideNamespace('IdentityAccess\Core', $allowedDependenciesInIdentityAccessCode))
        ->because('we want isolate our identity access core domain from external world.');


    $allowedDependenciesInIdentityAccessAdapters = array_merge($allowedPhpDependencies, $allowedVendorDependenciesInIdentityAccessAdapters);
    $identityAccessAdaptersIsolationRule = Rule::allClasses()
        //->except('IdentityAccess\AdapterForReadingAccounts', 'IdentityAccess\AdapterForReadingAccounts')
        ->that(new ResideInOneOfTheseNamespaces('IdentityAccess\Adapter*'))
        ->should(new NotHaveDependencyOutsideNamespace('IdentityAccess\Core', $allowedDependenciesInIdentityAccessAdapters))
        ->because('or namespaces in whitelist we want isolate our identity access Adapters from ever growing dependencies.');

    $config->add($identityAccessClassSet, $identityAccessCoreIsolationRule, $identityAccessAdaptersIsolationRule, ...$identityAccessPortAndAdapterArchitectureRules);


    /**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *
     *      Notifier Context
     *
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     */

    $notifierClassSet = ClassSet::fromDir(__DIR__ . '/_notifier/src');

    $allowedVendorDependenciesInNotifierCore = require_once __DIR__ . '/tools/phparkitect/VendorDependencies/allowed_in_notifier_core.php';
    $allowedVendorDependenciesInNotifierAdapters = require_once __DIR__ . '/tools/phparkitect/VendorDependencies/allowed_in_notifier_adapters.php';

    $notifierPortAndAdapterArchitectureRules = Architecture::withComponents()
        ->component('Core')->definedBy('Notifier\Core\*')
        ->component('Adapters')->definedBy('Notifier\AdapterFor*')
        ->component('Infrastructure')->definedBy('Notifier\Infrastructure\*')

        ->where('Infrastructure')->shouldNotDependOnAnyComponent()
        ->where('Adapters')->mayDependOnComponents('Core', 'Infrastructure')
        ->where('Core')->shouldNotDependOnAnyComponent()
        ->rules();

    $allowedDependenciesInNotifierCore = array_merge($allowedPhpDependencies, $allowedVendorDependenciesInNotifierCore);
    $notifierCoreIsolationRule = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('Notifier\Core'))
        ->should(new NotHaveDependencyOutsideNamespace('Notifier\Core', $allowedDependenciesInNotifierCore))
        ->because('we want isolate our notifier core domain from external world.');

    $allowedDependenciesInNotifierAdapters = array_merge($allowedPhpDependencies, $allowedVendorDependenciesInNotifierAdapters);
    $notifierAdaptersIsolationRule = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('Notifier\AdapterFor*'))
        ->should(new NotHaveDependencyOutsideNamespace('Notifier\Core', $allowedDependenciesInNotifierAdapters))
        ->because('we want isolate our notifier Adapters from ever growing dependencies.');

    $config->add($notifierClassSet, $notifierCoreIsolationRule, $notifierAdaptersIsolationRule, ...$notifierPortAndAdapterArchitectureRules);



    /**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *
     *      All Application
     *
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     *++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
     */

    $allApplicationClassSet = ClassSet::fromDir(__DIR__)
        ->excludePath('*tests*')
        ->excludePath('*vendor*')
        ->excludePath('*tools*')
        ->excludePath('*var*')
    ;

    $applicationModuleArchitectureRules = Architecture::withComponents()
        ->component('IdentityAccess')->definedBy('IdentityAccess\*')
        ->component('Notifier')->definedBy('Notifier\*')
        ->where('IdentityAccess')->shouldNotDependOnAnyComponent()
        ->where('Notifier')->shouldNotDependOnAnyComponent()
        ->rules();

    $config->add($allApplicationClassSet, ...$applicationModuleArchitectureRules);

};
