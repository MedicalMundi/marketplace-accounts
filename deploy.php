<?php
namespace Deployer;

require 'recipe/symfony.php';
require 'tools/deployer/recipes/marketplace-account.php';

// Config
set('allow_anonymous_stats', false);

set('repository', 'https://github.com/MedicalMundi/marketplace-accounts');

add('shared_files', [
    'config/jwt/private.key',
    'config/jwt/public.key',
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
    ->setLabels([
        'env' => 'production',
    ])
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
    ->setLabels([
        'env' => 'stage',
    ])
    ->set('deploy_path', '~/{{application_path_stage}}')
    ->set('http_user', 'ekvwxsme')
    ->set('writable_use_sudo', false)
    ->set('writable_mode', 'chmod')
    /** ssh settings */
    ->setRemoteUser('ekvwxsme')
    ->setPort(3508)
    //->set('identityFile', '~/.ssh/id_rsa_oe_modules_php_deployer')
    //->set('forwardAgent', true)
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
after('deploy', 'envvars:dump');
// Enable db migration
// Migrate database before symlink new release.
//before('deploy:symlink', 'database:migrate');
after('deploy', 'database:migrate');



/**
 *
 *  Gestione env vars
 *
 */
desc('Compiles .env files to .env.local.php.');
task('envvars:dump', function () {
//    if ('production' === get('labels')['env']){
//        writeln(' labels.env:' . get('labels')['env']);
//        info('Setup production env vars in file .env.local.php');
//        runLocally('cp -f .env.itroom.production .env.prod');
//        info('Generated env.dev with staging configuration data');
//
//        info('Run composer symfony:dump-env prod');
//        $cmdResult = runLocally('composer symfony:dump-env prod', ['tty' => true]);
//        echo $cmdResult;
//        info('Generated .env.local.php');
//    }elseif ('stage' === get('labels')['env']){
//        info('Setup stage env vars in file .env.local.php');
//        runLocally('cp -f .env.itroom.stage .env.dev');
//        info('Generated env.dev with staging configuration data');
//
//        info('Run composer symfony:dump-env dev');
//        $cmdResult = runLocally('composer symfony:dump-env dev', ['tty' => true]);
//        echo $cmdResult;
//        info('Generated .env.local.php');
//    }
//    info('Try to upload .env.local.php');
//    upload(__DIR__.'/.env.local.php', '{{release_path}}/.env.local.php');
//    info('Success: uploaded .env.local.php');
//
//    info('Cleanup local directories');
//    runLocally('rm -f .env.dev');
//    runLocally('rm -f .env.prod');
//    info('Remove generated .env.xxxx files from local filesystem');
//
//    runLocally('rm -f .env.local.php');
//    info('Remove generated .env.local.php from local filesystem');

    if ('production' === get('labels')['env']){
        /**
         * execute envvars:dump for production
         * when deployer run under
         * developer machine
         */
        if (! getenv('CI')){
            writeln(' labels.env:' . get('labels')['env']);
            info('Setup production env vars in file .env.local.php');
            runLocally('cp -f .env.itroom.production .env.prod');
            info('Generated env.dev with staging configuration data');

            info('Run composer symfony:dump-env prod');
            $cmdResult = runLocally('composer symfony:dump-env prod', ['tty' => true]);
            echo $cmdResult;
            info('Generated .env.local.php');
        }

        /**
         * execute envvars:dump for production
         * when deployer run under
         * CI pipeline (GHA)
         */
        if (getenv('CI')){
            info('GITHUB ACTION - Create and populate .env.dev file for production');
            $cmdResult = runLocally('ls -al');
            echo $cmdResult;

            info('Remove generic .env ');
            $cmdResult = runLocally('rm -f .env');
            echo $cmdResult;

            info('Generated env with production configuration data');
            $cmdResult = runLocally('touch .env');
            echo $cmdResult;


            $APP_ENV = getenv('APP_ENV');
            runLocally("echo APP_ENV=\"$APP_ENV\" >> .env");

            $APP_SECRET = getenv('APP_SECRET');
            runLocally("echo APP_SECRET=\"$APP_SECRET\" >> .env");

            $DATABASE_URL = getenv('DATABASE_URL');
            runLocally("echo DATABASE_URL=\"$DATABASE_URL\" >> .env");

            $LOCK_DSN = getenv('LOCK_DSN');
            runLocally("echo LOCK_DSN=\"$LOCK_DSN\" >> .env");

            $MAILER_DSN = getenv('MAILER_DSN');
            runLocally("echo MAILER_DSN=\"$MAILER_DSN\" >> .env");

            $OAUTH_GITHUB_CLIENT_ID = getenv('OAUTH_GITHUB_CLIENT_ID');
            runLocally("echo OAUTH_GITHUB_CLIENT_ID=\"$OAUTH_GITHUB_CLIENT_ID\" >> .env");

            $OAUTH_GITHUB_CLIENT_SECRET = getenv('OAUTH_GITHUB_CLIENT_SECRET');
            runLocally("echo OAUTH_GITHUB_CLIENT_SECRET=\"$OAUTH_GITHUB_CLIENT_SECRET\" >> .env");

            $OAUTH_OEMODULES_CLIENT_ID = getenv('OAUTH_OEMODULES_CLIENT_ID');
            runLocally("echo OAUTH_OEMODULES_CLIENT_ID=\"$OAUTH_OEMODULES_CLIENT_ID\" >> .env");

            $OAUTH_OEMODULES_CLIENT_SECRET = getenv('OAUTH_OEMODULES_CLIENT_SECRET');
            runLocally("echo OAUTH_OEMODULES_CLIENT_SECRET=\"$OAUTH_OEMODULES_CLIENT_SECRET\" >> .env");

            $SENTRY_DSN = getenv('SENTRY_DSN');
            runLocally("echo SENTRY_DSN=\"$SENTRY_DSN\" >> .env");
            $cmdResult = runLocally('cat .env');
            echo $cmdResult;

            info('Run composer symfony:dump-env prod');
            $cmdResult = runLocally('composer symfony:dump-env prod', ['tty' => true]);
            echo $cmdResult;
            info('Generated .env.local.php');


            $cmdResult = runLocally('cat .env.local.php');
            echo $cmdResult;
        }

    }elseif ('stage' === get('labels')['env']){

        if (! getenv('CI')){
            info('Setup stage env vars in file .env.local.php');
            runLocally('cp -f .env.itroom.stage .env.dev');
            info('Generated env.dev with staging configuration data');

            info('Run composer symfony:dump-env dev');
            $cmdResult = runLocally('composer symfony:dump-env dev', ['tty' => true]);
            echo $cmdResult;
            info('Generated .env.local.php');
        }

        if (getenv('CI')){
            info('GITHUB ACTION - Create and populate .env.dev file for stage');
            $cmdResult = runLocally('ls -al');
            echo $cmdResult;

            info('Remove generic .env.dev ');
            $cmdResult = runLocally('rm -f .env.dev');
            echo $cmdResult;

            info('Generated env.dev with staging configuration data');
            $cmdResult = runLocally('touch .env.dev');
            echo $cmdResult;


            $APP_ENV = getenv('APP_ENV');
            runLocally("echo APP_ENV=\"$APP_ENV\" >> .env.dev");

            $APP_SECRET = getenv('APP_SECRET');
            runLocally("echo APP_SECRET=\"$APP_SECRET\" >> .env.dev");

            $DATABASE_URL = getenv('DATABASE_URL');
            runLocally("echo DATABASE_URL=\"$DATABASE_URL\" >> .env.dev");

            $LOCK_DSN = getenv('LOCK_DSN');
            runLocally("echo LOCK_DSN=\"$LOCK_DSN\" >> .env.dev");

            $MAILER_DSN = getenv('MAILER_DSN');
            runLocally("echo MAILER_DSN=\"$MAILER_DSN\" >> .env.dev");

            $CORS_ALLOW_ORIGIN = getenv('CORS_ALLOW_ORIGIN');
            runLocally("echo CORS_ALLOW_ORIGIN=\"$CORS_ALLOW_ORIGIN\" >> .env.dev");

            $cmdResult = runLocally('cat .env.dev');
            echo $cmdResult;

            info('Run composer symfony:dump-env dev');
            $cmdResult = runLocally('composer symfony:dump-env dev', ['tty' => true]);
            echo $cmdResult;
            info('Generated .env.local.php');


            $cmdResult = runLocally('cat .env.local.php');
            echo $cmdResult;
        }

//        info('Run composer symfony:dump-env dev');
//        $cmdResult = runLocally('composer symfony:dump-env dev', ['tty' => true]);
//        echo $cmdResult;
//        info('Generated .env.local.php');
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
