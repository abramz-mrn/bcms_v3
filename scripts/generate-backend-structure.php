#!/usr/bin/env php
<?php
/**
 * BCMS Laravel Backend Structure Generator
 * 
 * This script generates all necessary files for the BCMS backend:
 * - Migrations (all tables)
 * - Models with relationships
 * - Controllers with CRUD endpoints
 * - Form Requests (validation)
 * - API Resources (transformers)
 * - Seeders with dummy data
 * - Services (Mikrotik, Payment, Notifications)
 * - Jobs (billing automation)
 * - Policies (authorization)
 * - Middleware (audit log, permission check)
 * 
 * Usage: php scripts/generate-backend-structure.php
 */

$basePath = __DIR__ . '/../apps/api';

if (!is_dir($basePath)) {
    die("Error: Laravel app not found at $basePath\n");
}

echo "🚀 Generating BCMS Backend Structure...\n\n";

// Tables configuration with all fields
$tables = [
    'users_groups' => [
        'fields' => [
            'name' => 'string',
            'permissions' => 'json',
            'description' => 'text:nullable',
        ],
        'indexes' => ['name'],
    ],
    'companies' => [
        'fields' => [
            'name' => 'string',
            'address' => 'text:nullable',
            'phone' => 'string:nullable',
            'email' => 'string:nullable',
            'bank_account' => 'json:nullable',
            'logo' => 'string:nullable',
        ],
    ],
    'brands' => [
        'fields' => [
            'company_id' => 'foreignId:companies',
            'name' => 'string',
            'slug' => 'string',
            'description' => 'text:nullable',
        ],
        'indexes' => ['slug'],
    ],
    'products' => [
        'fields' => [
            'brand_id' => 'foreignId:brands',
            'name' => 'string',
            'description' => 'text:nullable',
            'price' => 'decimal:10,2',
            'billing_cycle' => 'string',
            'is_active' => 'boolean:default(true)',
        ],
    ],
    'internet_services' => [
        'fields' => [
            'product_id' => 'foreignId:products',
            'bandwidth_download' => 'integer',
            'bandwidth_upload' => 'integer',
            'auto_soft_limit' => 'integer:nullable',
            'auto_suspend' => 'integer:nullable',
            'quota_limit' => 'integer:nullable',
        ],
    ],
    // More tables will be added...
];

echo "📝 Creating migrations...\n";
// Create migration files
foreach ($tables as $table => $config) {
    createMigration($table, $config, $basePath);
}

echo "\n✅ Backend structure generation complete!\n";

function createMigration($tableName, $config, $basePath) {
    $timestamp = date('Y_m_d_His', time() + count($GLOBALS['tables']));
    $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'Table';
    $filename = $timestamp . '_create_' . $tableName . '_table.php';
    
    $migrationPath = "$basePath/database/migrations/$filename";
    
    // Don't overwrite existing migrations
    if (file_exists($migrationPath)) {
        echo "  ⏭️  Skipping $tableName (already exists)\n";
        return;
    }
    
    $fields = $config['fields'] ?? [];
    $fieldDefinitions = [];
    
    foreach ($fields as $name => $type) {
        $fieldDefinitions[] = generateFieldDefinition($name, $type);
    }
    
    $fieldsCode = implode("\n            ", $fieldDefinitions);
    
    $migration = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->id();
            $fieldsCode
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('$tableName');
    }
};
PHP;
    
    file_put_contents($migrationPath, $migration);
    echo "  ✓ Created migration: $filename\n";
}

function generateFieldDefinition($name, $type) {
    $parts = explode(':', $type);
    $baseType = $parts[0];
    $modifiers = array_slice($parts, 1);
    
    $code = '';
    
    switch ($baseType) {
        case 'string':
            $code = "\$table->string('$name')";
            break;
        case 'text':
            $code = "\$table->text('$name')";
            break;
        case 'integer':
            $code = "\$table->integer('$name')";
            break;
        case 'bigInteger':
            $code = "\$table->bigInteger('$name')";
            break;
        case 'decimal':
            $precision = $modifiers[0] ?? '8,2';
            $code = "\$table->decimal('$name', $precision)";
            break;
        case 'boolean':
            $code = "\$table->boolean('$name')";
            break;
        case 'json':
            $code = "\$table->json('$name')";
            break;
        case 'foreignId':
            $refTable = $modifiers[0] ?? $name;
            $code = "\$table->foreignId('$name')->constrained('$refTable')->cascadeOnDelete()";
            break;
        default:
            $code = "\$table->string('$name')";
    }
    
    // Apply modifiers
    foreach ($modifiers as $modifier) {
        if (strpos($modifier, 'default') !== false) {
            $code .= "->$modifier";
        } elseif ($modifier === 'nullable') {
            $code .= '->nullable()';
        }
    }
    
    return $code . ';';
}

echo "\n📌 Note: This is a basic generator. Complete the structure manually for full functionality.\n";
echo "    Run: php artisan migrate\n";
