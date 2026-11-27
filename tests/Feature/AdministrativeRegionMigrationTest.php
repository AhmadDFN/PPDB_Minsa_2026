<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdministrativeRegionMigrationTest extends TestCase
{
    public function test_mysql_creates_unique_index_before_self_referencing_foreign_key(): void
    {
        $connection = DB::connection('mysql');
        DB::setDefaultConnection('mysql');

        try {
            $statements = $connection->pretend(function (): void {
                $migration = require database_path('migrations/2026_11_26_140000_create_administrative_regions_table.php');
                $migration->up();
            });
        } finally {
            DB::setDefaultConnection('sqlite');
        }

        $sql = array_column($statements, 'query');
        $unique = null;
        $foreign = null;
        foreach ($sql as $index => $query) {
            if (str_contains($query, 'ar_code_uq')) {
                $unique = $index;
            }
            if (str_contains($query, 'ar_parent_fk')) {
                $foreign = $index;
            }
        }

        $this->assertNotNull($unique);
        $this->assertNotNull($foreign);
        $this->assertLessThan($foreign, $unique);
    }
}
