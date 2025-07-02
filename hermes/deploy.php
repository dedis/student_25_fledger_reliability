<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'git@github.com:aizensoosuke/fledger-dashboard');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', ['caddy']);

// set('writable_mode', 'skip');

// Tasks
task('deploy:info')->verbose();

task(
    'npm:build',
    function () {
        runLocally('npm run build');
    }
);
task(
    'npm:upload',
    function () {
        run('mkdir -p {{release_path}}/public/build');
        upload('public/build/', '{{release_path}}/public/build/');
    }
);
task('artisan:deploy:permissions', artisan('deploy:permissions'));
task('artisan:deploy:scout', artisan('deploy:scout'));

task('horizon:restart', fn () => run('sudo supervisorctl restart horizon'));
task('octane:restart', fn () => run('sudo systemctl restart octane@fledger'));

// Hosts

host('production')
    ->set('hostname', 'fledger-dashboard')
    ->set('branch', 'production')
    ->set('php_version', '8.3')
    ->set('deploy_path', '/srv/fledger');

// Hooks

task('deploy:prepare')
    ->addAfter('npm:build')
    ->addAfter('npm:upload');

task('artisan:migrate')
    // ->addAfter('artisan:deploy:scout')
    ->addAfter('artisan:deploy:permissions');

task('deploy:success')
    ->addAfter('octane:restart')
    ->addAfter('horizon:restart');

after('deploy:failed', 'deploy:unlock');
