<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationMigrationsTest extends TestCase
{
    public function test_fresh_migrations_create_the_application_schema(): void
    {
        Schema::dropAllTables();

        $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);

        $this->assertSame(0, $exitCode, Artisan::output());
    }

    public function test_rolling_back_both_two_factor_migrations_succeeds(): void
    {
        Schema::dropAllTables();
        Schema::create('utilizadores', function (Blueprint $table): void {
            $table->id();
        });

        $original = require database_path('migrations/2026_11_04_164230_add_two_factor_fields_to_utilizadores_table.php');
        $duplicate = require database_path('migrations/2026_11_25_144647_add_two_factor_columns_to_utilizadores_table.php');

        $original->up();
        $duplicate->up();
        $duplicate->down();
        $original->down();

        $this->assertFalse(Schema::hasColumn('utilizadores', 'two_factor_secret'));
    }
}
