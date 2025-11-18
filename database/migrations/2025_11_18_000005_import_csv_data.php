<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Run the Artisan command to import CSV data
        Artisan::call('csv:import');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can define logic to reverse the migration
        // For example, truncate the tables if necessary
    }
};