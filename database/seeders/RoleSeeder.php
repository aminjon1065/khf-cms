<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const ROLES = ['admin', 'editor'];

    public function run(): void
    {
        foreach (self::ROLES as $roleName) {
            Role::query()->firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
            );
        }
    }
}
