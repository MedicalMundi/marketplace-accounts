<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('allow_anonymous_stats', false);

set('repository', 'https://github.com/MedicalMundi/marketplace-accounts');

add('shared_files', []);
add('shared_dirs', [
    'var/log',
]);
add('writable_dirs', []);
// Writable dirs by web server
add('writable_dirs', [
    'var',
]);


/**
 *
 * HOSTS CONFIGURATION ( production & stage )
 *
 */


/** Production Application path on hosting server  */
set('application_path_production', 'marketplace.oe-modules.com');

/** Production Hosts configuration */
host('production')
    ->setHostname('accounts.oe-modules.com')
    ->set('stage', 'production')
    ->set('deploy_path', '~/{{application_path_production}}')
    ->set('http_user', 'ekvwxsme')
    ->set('writable_use_sudo', false)
    ->set('writable_mode', 'chmod')
    /** ssh settings */
    ->setRemoteUser('ekvwxsme')
    ->setPort(3508)
    ->set('identityFile', '~/.ssh/id_rsa_oe_modules_php_deployer')
    ->set('ssh_multiplexing', false)
    /** git & composer settings */
    ->set('branch', 'main')
    ->set('composer_options', ' --prefer-dist --no-dev --no-progress --no-interaction --optimize-autoloader')
    ->set('keep_releases', 5)
;


/** Staging Application path on hosting server  */
set('application_path_stage', 'stage.accounts.oe-modules.com');

/** Staging Hosts configuration */
host('stage')
    ->setHostname('stage.accounts.oe-modules.com')
    ->set('stage', 'stage')
    ->set('deploy_path', '~/{{application_path_stage}}')
    ->set('http_user', 'ekvwxsme')
    ->set('writable_use_sudo', false)
    ->set('writable_mode', 'chmod')
    /** ssh settings */
    ->setRemoteUser('ekvwxsme')
    ->setPort(3508)
    //->set('identityFile', '~/.ssh/id_rsa_oe_modules_php_deployer')
    ->set('ssh_multiplexing', false)
    /** git & composer settings */
    ->set('branch', 'main')
    ->set('composer_options', ' --prefer-dist --no-progress --no-interaction --optimize-autoloader')
    ->set('keep_releases', 2)
;


/**
 *  DEPLOYER HOOKS
 */

after('deploy:failed', 'deploy:unlock');


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
