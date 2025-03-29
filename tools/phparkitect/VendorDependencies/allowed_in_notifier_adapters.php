<?php declare(strict_types=1);

return [
    'Notifier\Core',

    'Psr\Log\LoggerInterface',
    'Psr\Cache\CacheItemPoolInterface',

    'Ecotone\Modelling\QueryBus',
    'Ecotone\Messaging\Attribute\Asynchronous',
    'Ecotone\Modelling\CommandBus',

    'Doctrine\DBAL\Connection',

    'Symfony\Component\Mailer\MailerInterface',

    'Symfony\Bridge\Twig\Mime\TemplatedEmail',

    'Symfony\Component\Mime\Address',

];
