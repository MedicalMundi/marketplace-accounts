<?php

namespace Deployer;

set('projections', []);
add('projections', [
    //'my.projectionName',
]);



/**
 *
 *  Gestione delle projections (eventStore - eventSourcing)
 *
 */
desc('Run "bin/console ecotone:es:initialize-projection" on the host.');
task('projection:initialize', function () {
    info('Initialize eventstore projections');

    if (!has('projections')) {
        warning("Please, specify \"projection\" to initialize.");
        return;
    }

    $projections = get('projections');
    foreach ($projections as $projection) {
        info('Current projection: ' . $projection);
        $output = run('cd {{release_or_current_path}} && {{bin/console}} ecotone:es:initialize-projection ' . $projection, ['tty' => true]);
        echo $output;
    }
});


/**
 *
 *  Gestione modalità di manutenzione
 *  sia soft che hard
 *
 */

/**
 * MAINTENANCE BUNDLE CONFIGURATION
 *
 *  LOCK
 * @see https://packagist.org/packages/corley/maintenance-bundle
 */
desc('Maintenance on');
task('maintenance:on', function () {
    run('{{bin/console}} corley:maintenance:lock on');
    info('Maintenance mode (hard-lock) successfully activated!');
});

desc('Maintenance off');
task('maintenance:off', function () {
    run('{{bin/console}} corley:maintenance:lock off');
    info('Maintenance mode (hard-lock) was deactivated!');
});


/**
 * MAINTENANCE BUNDLE CONFIGURATION
 *
 *  SOFT-LOCK
 * @see https://packagist.org/packages/corley/maintenance-bundle
 */
desc('Maintenance soft-lock on');
task('maintenance:soft:on', function () {
    run('{{bin/console}} corley:maintenance:soft-lock on');
    info('Maintenance mode (soft-lock) successfully activated!');

});

desc('Maintenance soft-lock off');
task('maintenance:soft:off', function () {
    run('{{bin/console}} corley:maintenance:soft-lock off');
    info('Maintenance mode (soft-lock) was deactivated!');
});


