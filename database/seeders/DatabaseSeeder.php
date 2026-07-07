<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            NewsCategorySeeder::class,
            RegionSeeder::class,
            NewsSeeder::class,
            SlideSeeder::class,
            HomeContentSeeder::class,
            DocumentSeeder::class,
            StructureSeeder::class,
            ActivitySeeder::class,
            MapSeeder::class,
            ContactSeeder::class,
            ForumSeeder::class,
        ]);

        $this->seedAdministrator();
    }

    /**
     * Provision the first administrator from config/env. Fully idempotent: the
     * account's name, password and active flag are set ONLY on first creation,
     * so re-running `db:seed` never clobbers panel edits or a rotated password.
     * Every run (re)asserts the admin role so it survives a role re-seed.
     * Credentials come from .env (ToR §4, §10).
     */
    private function seedAdministrator(): void
    {
        /** @var array{name: string, email: string, password: ?string} $config */
        $config = config('khf.admin');

        $admin = User::query()->firstOrCreate(
            ['email' => $config['email']],
            $this->newAdministratorAttributes($config),
        );

        $admin->syncRoles(['admin']);
    }

    /**
     * Attributes for a freshly created administrator. With no ADMIN_PASSWORD:
     * locally the well-known dev password "password" is used so the Filament
     * panel is instantly reachable (admin@khf.tj / password); outside local a
     * strong random one is generated and printed once (never a known secret).
     *
     * @param  array{name: string, email: string, password: ?string}  $config
     * @return array<string, mixed>
     */
    private function newAdministratorAttributes(array $config): array
    {
        $password = $config['password'];

        if (blank($password)) {
            if (app()->isLocal()) {
                $password = 'password';
            } else {
                $password = Str::password(16);

                $this->command?->warn("Generated admin password for {$config['email']}: {$password}");
                $this->command?->warn('Store it now — it will not be shown again.');
            }
        }

        return [
            'name' => $config['name'],
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
        ];
    }
}
