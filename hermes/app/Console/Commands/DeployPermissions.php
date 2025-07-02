<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DeployPermissions extends Command
{
    protected $signature = 'deploy:permissions';

    protected $description = 'Deploy permissions for the application';

    public function handle()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $models = collect([
            'user',
            'experiment',
            'node',
            'data point',
            'timeless data point',
        ]);

        $customPermissions = collect([
            'access filament',
            'access pulse',
            'access horizon',
            'end experiments',
        ]);

        $adminExceptions = collect([
            //
        ]);

        $runnerPermissions = collect([
            'create experiments',
            'create nodes',
            'create data points',
            'create timeless data points',

            'update nodes',
            'update experiments',

            'view experiments',

            'end experiments',
        ]);

        $base = collect([
            'create',
            'view',
            'update',
            'soft delete',
            'delete',
            'restore',
        ]);

        $baseSingular = collect([
            'view any',
        ]);

        $modelPermissions =
            $base->crossJoin($models->map(fn ($model) => str($model)->plural()))
                ->mapSpread(fn ($base, $model) => ['name' => "{$base} {$model}", 'guard_name' => 'web']);
        $singularModelPermissions =
            $baseSingular->crossJoin($models)
                ->mapSpread(fn ($base, $model) => ['name' => "{$base} {$model}", 'guard_name' => 'web']);

        $otherPermissions = $customPermissions
            ->map(fn ($permission) => ['name' => $permission, 'guard_name' => 'web']);

        $allPermissions =
            $modelPermissions
                ->merge($singularModelPermissions)
                ->merge($otherPermissions)
                ->toArray();
        Permission::upsert($allPermissions, ['name', 'guard_name']);

        $adminRole = Role::createOrFirst(['name' => 'admin']);
        $runnerRole = Role::createOrFirst(['name' => 'runner']);

        $adminRole->syncPermissions(Permission::whereNotIn('name', $adminExceptions)->get());
        $runnerRole->syncPermissions(Permission::whereIn('name', $runnerPermissions)->get());

        $admin = User::firstWhere('name', 'admin');
        if ($admin) {
            $admin->assignRole('admin');
        }

        $runner = User::firstWhere('name', 'runner');
        if ($runner) {
            $runner->assignRole('runner');
        }
    }
}
