<?php
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
foreach ($tables as $table) {
    echo "TABLE: " . $table->name . "\n";
    $columns = DB::select("PRAGMA table_info(" . $table->name . ")");
    foreach ($columns as $column) {
        echo "  - " . $column->name . " | Type: " . $column->type . " | Nullable: " . ($column->notnull ? 'No' : 'Yes') . " | Default: " . ($column->dflt_value ?? 'NULL') . "\n";
    }
    echo "\n";
}
