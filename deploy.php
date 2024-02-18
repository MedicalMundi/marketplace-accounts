<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('allow_anonymous_stats', false);

set('repository', 'https://github.com/MedicalMundi/marketplace-accounts');

add('shared_files', [
    'config/jwt/private.pem',
    'config/jwt/public.pem',
    '.env.local'
]);
add('shared_dirs', [
    'var/log',
    'var/sessions',
]);
add('writable_dirs', [
    'var',
]);


/**
 *
 * HOSTS CONFIGURATION ( production & stage )
 *
 */


/** Production Application path on hosting server  */
set('application_path_production', 'auth.oe-modules.com');

/** Production Hosts configuration */
host('production')
    ->setHostname('auth.oe-modules.com')
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
set('application_path_stage', 'stage.auth.oe-modules.com');

/** Staging Hosts configuration */
host('stage')
    ->setHostname('stage.auth.oe-modules.com')
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

///** Staging Application path on hosting server  */
//set('application_path_stage', 'stage.accounts.oe-modules.com');
//
///** Staging Hosts configuration */
//host('stage')
//    ->setHostname('stage.accounts.oe-modules.com')
//    ->set('stage', 'stage')
//    ->set('deploy_path', '~/{{application_path_stage}}')
//    ->set('http_user', 'ekvwxsme')
//    ->set('writable_use_sudo', false)
//    ->set('writable_mode', 'chmod')
//    /** ssh settings */
//    ->setRemoteUser('ekvwxsme')
//    ->setPort(3508)
//    //->set('identityFile', '~/.ssh/id_rsa_oe_modules_php_deployer')
//    ->set('ssh_multiplexing', false)
//    /** git & composer settings */
//    ->set('branch', 'main')
//    ->set('composer_options', ' --prefer-dist --no-progress --no-interaction --optimize-autoloader')
//    ->set('keep_releases', 2)
//;


/**
 *  DEPLOYER HOOKS
 */

after('deploy:failed', 'deploy:unlock');
after('deploy', 'envvars:dump');
// Enable db migration
// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');


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



desc('Compiles .env files to .env.local.php.');
task('envvars:dump', function () {
    if ('production' === get('labels')['env']){
        writeln(' labels.env:' . get('labels')['env']);
        info('Setup production env vars in file .env.local.php');
        runLocally('cp -f .env.itroom.production .env.prod');
        info('Generated env.dev with staging configuration data');

        info('Run composer symfony:dump-env prod');
        $cmdResult = runLocally('composer symfony:dump-env prod', ['tty' => true]);
        echo $cmdResult;
        info('Generated .env.local.php');
    }elseif ('stage' === get('labels')['env']){
        info('Setup stage env vars in file .env.local.php');
        runLocally('cp -f .env.itroom.stage .env.dev');
        info('Generated env.dev with staging configuration data');

        info('Run composer symfony:dump-env dev');
        $cmdResult = runLocally('composer symfony:dump-env dev', ['tty' => true]);
        echo $cmdResult;
        info('Generated .env.local.php');
    }

    info('Try to upload .env.local.php');
    upload(__DIR__.'/.env.local.php', '{{release_path}}/.env.local.php');
    info('Success: uploaded .env.local.php');

    info('Cleanup local directories');
    runLocally('rm -f .env.dev');
    runLocally('rm -f .env.prod');
    info('Remove generated .env.xxxx files from local filesystem');

    runLocally('rm -f .env.local.php');
    info('Remove generated .env.local.php from local filesystem');
});