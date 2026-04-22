<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidateSalePostingsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale:validate-postings-table';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Validate and auto-create/fix the sale_postings table with all required columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking sale_postings table...');

        $requiredColumns = [
            'id' => 'primary key',
            'sale_id' => 'foreign key',
            'product_id' => 'foreign key',
            'qty' => 'integer',
            'source_type' => 'enum (branch, warehouse)',
            'source_id' => 'integer (nullable)',
            'status' => 'enum (pending, processed)',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];

        // Step 1: Check if table exists
        if (!Schema::hasTable('sale_postings')) {
            $this->warn('⚠️  sale_postings table does NOT exist!');
            $this->info('📝 Running migration to create table...');
            
            $this->call('migrate', [
                '--path' => 'database/migrations/2026_03_27_104042_create_sale_postings_table.php'
            ]);

            $this->info('✅ sale_postings table created successfully!');
            return Command::SUCCESS;
        }

        // Step 2: Table exists - validate columns
        $this->info('✅ sale_postings table exists');
        $this->info('📋 Validating columns...');

        $existingColumns = DB::getSchemaBuilder()->getColumnListing('sale_postings');
        $missingColumns = array_diff(array_keys($requiredColumns), $existingColumns);

        if (empty($missingColumns)) {
            $this->info('✅ All required columns present!');
            
            $this->table(
                ['Column', 'Description', 'Status'],
                array_map(function($col, $desc) {
                    return [$col, $desc, '✅ Present'];
                }, array_keys($requiredColumns), array_values($requiredColumns))
            );

            return Command::SUCCESS;
        }

        // Step 3: Some columns missing - add them
        $this->warn('❌ Missing ' . count($missingColumns) . ' column(s): ' . implode(', ', $missingColumns));
        $this->info('🔧 Adding missing columns...');

        Schema::table('sale_postings', function ($table) use ($missingColumns) {
            if (in_array('sale_id', $missingColumns)) {
                $table->unsignedBigInteger('sale_id')->index();
            }
            if (in_array('product_id', $missingColumns)) {
                $table->unsignedBigInteger('product_id')->index();
            }
            if (in_array('qty', $missingColumns)) {
                $table->integer('qty')->default(0);
            }
            if (in_array('source_type', $missingColumns)) {
                $table->enum('source_type', ['branch', 'warehouse'])->default('branch');
            }
            if (in_array('source_id', $missingColumns)) {
                $table->unsignedBigInteger('source_id')->nullable();
            }
            if (in_array('status', $missingColumns)) {
                $table->enum('status', ['pending', 'processed'])->default('pending');
            }
        });

        $this->info('✅ Missing columns added successfully!');

        $this->table(
            ['Column', 'Description', 'Status'],
            array_map(function($col, $desc) use ($missingColumns) {
                $status = in_array($col, $missingColumns) ? '✅ Added' : '✅ Existing';
                return [$col, $desc, $status];
            }, array_keys($requiredColumns), array_values($requiredColumns))
        );

        return Command::SUCCESS;
    }
}
