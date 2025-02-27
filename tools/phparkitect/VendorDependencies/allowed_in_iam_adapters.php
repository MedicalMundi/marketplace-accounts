<?php declare(strict_types=1);

return [
    'IdentityAccess\Core',
    'IdentityAccess\Adapter*',

    'Ecotone\Modelling\QueryBus',

    'League\Bundle\OAuth2ServerBundle',

    'Doctrine\DBAL\Connection',
    'Doctrine\ORM\EntityManagerInterface',

    'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
    'Symfony\Component\HttpFoundation\Request',
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\Security\Http\Attribute\IsGranted',

    'Symfony\Component\Console',

    'Symfony\Component\Form',

    'Symfony\Component\OptionsResolver\OptionsResolver',

    'Symfony\Component\Routing\Annotation\Route',
];
