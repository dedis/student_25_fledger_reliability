<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasDefaultPolicy
{
    protected static function singularPermissionName(): string
    {
        return str(static::class)
            ->classBasename()
            ->replaceLast('Policy', '')
            ->kebab()
            ->replace('-', ' ');
    }

    protected static function pluralPermissionName(): string
    {
        return str(static::singularPermissionName())->plural();
    }

    protected static function singularPermissionTo(string $permissionTo): string
    {
        $permissionName = static::singularPermissionName();

        return "{$permissionTo} {$permissionName}";
    }

    protected static function pluralPermissionTo(string $permissionTo): string
    {
        $permissionName = static::pluralPermissionName();

        return "{$permissionTo} {$permissionName}";
    }

    public function viewAny(User $user): bool
    {
        return $user->can(static::singularPermissionTo('view any'));
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can(static::pluralPermissionTo('view'))
            || ($user->can(static::pluralPermissionTo('view owned'))
                && method_exists($model, 'isOwnedBy')
                && $model->isOwnedBy($user));
    }

    public function create(User $user): bool
    {
        return $user->can(static::pluralPermissionTo('create'));
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can(static::pluralPermissionTo('update'))
            || ($user->can(static::pluralPermissionTo('update owned'))
                && method_exists($model, 'isOwnedBy')
                && $model->isOwnedBy($user));
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can(static::pluralPermissionTo('soft delete'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(static::pluralPermissionTo('soft delete'));
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can(static::pluralPermissionTo('restore'));
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(static::pluralPermissionTo('restore'));
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $user->can(static::pluralPermissionTo('force delete'));
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(static::pluralPermissionTo('force delete'));
    }

    public function replicate(User $user, Model $model): bool
    {
        return false;
    }
}
