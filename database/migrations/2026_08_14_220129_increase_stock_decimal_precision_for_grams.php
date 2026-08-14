<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * decimal(12,3) on a kg-denominated column only resolves to 1 gram
 * (0.001 kg) — a 0.5g sample (0.0005 kg) was rounding up to 1g. Widening to
 * decimal(12,4) resolves down to 0.1g, which is what StockService's
 * conversion now rounds to. Defaults are repeated explicitly because MySQL
 * drops a column's default when MODIFY doesn't restate it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE distributor_stock_items MODIFY received_quantity DECIMAL(12,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE distributor_stock_items MODIFY current_quantity DECIMAL(12,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE distributor_stock_items MODIFY returned_quantity DECIMAL(12,4) NULL');

        DB::statement('ALTER TABLE stock_movements MODIFY quantity_input DECIMAL(12,4) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY quantity_stock_unit DECIMAL(12,4) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY balance_before DECIMAL(12,4) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY balance_after DECIMAL(12,4) NOT NULL');

        DB::statement('ALTER TABLE sample_items MODIFY quantity_input DECIMAL(12,4) NOT NULL');
        DB::statement('ALTER TABLE sample_items MODIFY quantity_stock_unit DECIMAL(12,4) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE distributor_stock_items MODIFY received_quantity DECIMAL(12,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE distributor_stock_items MODIFY current_quantity DECIMAL(12,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE distributor_stock_items MODIFY returned_quantity DECIMAL(12,3) NULL');

        DB::statement('ALTER TABLE stock_movements MODIFY quantity_input DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY quantity_stock_unit DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY balance_before DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE stock_movements MODIFY balance_after DECIMAL(12,3) NOT NULL');

        DB::statement('ALTER TABLE sample_items MODIFY quantity_input DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE sample_items MODIFY quantity_stock_unit DECIMAL(12,3) NOT NULL');
    }
};
