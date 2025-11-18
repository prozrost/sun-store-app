<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create expression GIN indexes on a combined text vector (name, manufacturer, description)
        DB::statement("CREATE INDEX IF NOT EXISTS idx_batteries_search_tsv ON batteries USING GIN (to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(manufacturer,'') || ' ' || coalesce(description,'')))");

        // Connectors
        DB::statement("CREATE INDEX IF NOT EXISTS idx_connectors_search_tsv ON connectors USING GIN (to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(manufacturer,'') || ' ' || coalesce(description,'')))");

        // Solar panels
        DB::statement("CREATE INDEX IF NOT EXISTS idx_solar_panels_search_tsv ON solar_panels USING GIN (to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(manufacturer,'') || ' ' || coalesce(description,'')))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_batteries_search_tsv');
        DB::statement('DROP INDEX IF EXISTS idx_connectors_search_tsv');
        DB::statement('DROP INDEX IF EXISTS idx_solar_panels_search_tsv');
    }
};
