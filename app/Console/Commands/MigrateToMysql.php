<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToMysql extends Command
{
    protected $signature = 'app:migrate-to-mysql {--fresh : Drop all MySQL tables before migrating}';

    protected $description = 'Migrate all table data from PostgreSQL (setuprintersnewDB) to MySQL (setuprintersDB)';

    public function handle()
    {
        $this->info('Starting migration from PostgreSQL to MySQL...');

        // Step 1: Run Laravel migrations on MySQL to create schema
        $this->info('');
        $this->info('Step 1: Creating schema in MySQL...');

        $migrateCommand = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
        $this->call($migrateCommand, ['--database' => 'mysql', '--force' => true]);

        $this->info('MySQL schema created successfully.');

        // Step 2: Get tables to migrate (only data tables, skip system tables)
        $tablesToMigrate = [
            'users',
            'settings',
        ];

        // Step 3: Copy data from PostgreSQL to MySQL
        $this->info('');
        $this->info('Step 2: Copying data from PostgreSQL to MySQL...');

        foreach ($tablesToMigrate as $table) {
            $this->migrateTable($table);
        }

        // Step 3: Copy scanned_barcodes from old pgsql2 (setuprintersDB -> setu_printers schema)
        $this->info('');
        $this->info('Step 3: Copying scanned_barcodes from old PostgreSQL (pgsql2)...');
        $this->migrateTable('scanned_barcodes', 'pgsql2');

        $this->info('');
        $this->info('Migration completed successfully!');

        return Command::SUCCESS;
    }

    private function migrateTable(string $table, string $sourceConnection = 'pgsql'): void
    {
        if (!Schema::connection($sourceConnection)->hasTable($table)) {
            $this->warn("  Skipping '{$table}' - table does not exist in {$sourceConnection}.");
            return;
        }

        if (!Schema::connection('mysql')->hasTable($table)) {
            $this->warn("  Skipping '{$table}' - table does not exist in MySQL.");
            return;
        }

        $count = DB::connection($sourceConnection)->table($table)->count();
        $this->info("  Migrating '{$table}' from {$sourceConnection} ({$count} rows)...");

        if ($count === 0) {
            $this->info("  '{$table}' is empty, skipping.");
            return;
        }

        // Truncate MySQL table first to avoid duplicates
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::connection('mysql')->table($table)->truncate();

        // Get MySQL columns to only insert matching columns
        $mysqlColumns = Schema::connection('mysql')->getColumnListing($table);

        // Process in chunks to handle large tables
        DB::connection($sourceConnection)->table($table)->orderBy(
            Schema::connection($sourceConnection)->hasColumn($table, 'id') ? 'id' : array_key_first(
                array_flip(Schema::connection($sourceConnection)->getColumnListing($table))
            )
        )->chunk(500, function ($rows) use ($table, $mysqlColumns) {
            $insertData = [];

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                // Only keep columns that exist in MySQL table
                $filtered = array_intersect_key($rowArray, array_flip($mysqlColumns));
                $insertData[] = $filtered;
            }

            if (!empty($insertData)) {
                DB::connection('mysql')->table($table)->insert($insertData);
            }
        });

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $newCount = DB::connection('mysql')->table($table)->count();
        $this->info("  '{$table}' migrated: {$newCount} rows inserted.");
    }
}
