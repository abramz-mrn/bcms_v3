#!/bin/bash
# Create all BCMS database migrations

API_PATH="apps/api/database/migrations"
TIMESTAMP=$(date +"%Y_%m_%d_%H%M%S")

cd "$(dirname "$0")/.."

# Function to create migration
create_migration() {
    local table_name="$1"
    local fields="$2"
    local class_name=$(echo "$table_name" | sed -r 's/(^|_)([a-z])/\U\2/g' | sed 's/_//g')
    local filename="${API_PATH}/${TIMESTAMP}_create_${table_name}_table.php"
    
    cat > "$filename" << EOF
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('$table_name', function (Blueprint \$table) {
            \$table->id();
${fields}
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('$table_name');
    }
};
EOF

    echo "Created: $filename"
    TIMESTAMP=$(($TIMESTAMP + 1))
}

echo "Creating BCMS database migrations..."

# 1. users_groups
create_migration "users_groups" "            \$table->string('name')->unique();
            \$table->json('permissions');
            \$table->text('description')->nullable();"

# 2. Modify users table  
cat > "${API_PATH}/$(($TIMESTAMP))_modify_users_table.php" << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('users_group_id')->after('id')->constrained('users_groups')->cascadeOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['users_group_id']);
            $table->dropColumn(['users_group_id', 'phone', 'address', 'is_active']);
        });
    }
};
EOF

echo "Created: users table modification"
TIMESTAMP=$(($TIMESTAMP + 1))

# 3. companies
create_migration "companies" "            \$table->string('name');
            \$table->text('address')->nullable();
            \$table->string('phone')->nullable();
            \$table->string('email')->nullable();
            \$table->json('bank_account')->nullable();
            \$table->string('logo')->nullable();"

# 4. brands
create_migration "brands" "            \$table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            \$table->string('name');
            \$table->string('slug')->unique();
            \$table->text('description')->nullable();"

# 5. products
create_migration "products" "            \$table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            \$table->string('name');
            \$table->text('description')->nullable();
            \$table->decimal('price', 10, 2);
            \$table->string('billing_cycle');
            \$table->boolean('is_active')->default(true);"

# 6. internet_services
create_migration "internet_services" "            \$table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            \$table->integer('bandwidth_download');
            \$table->integer('bandwidth_upload');
            \$table->integer('auto_soft_limit')->nullable();
            \$table->integer('auto_suspend')->nullable();
            \$table->integer('quota_limit')->nullable();"

# 7. promotions  
create_migration "promotions" "            \$table->string('name');
            \$table->text('description')->nullable();
            \$table->enum('type', ['percentage', 'fixed']);
            \$table->decimal('value', 10, 2);
            \$table->date('start_date');
            \$table->date('end_date');
            \$table->boolean('is_active')->default(true);"

# 8. routers
create_migration "routers" "            \$table->string('name');
            \$table->string('ip_address');
            \$table->integer('api_port')->default(8729);
            \$table->integer('ssh_port')->default(22);
            \$table->string('username');
            \$table->string('password');
            \$table->enum('status', ['online', 'offline', 'error'])->default('offline');
            \$table->json('config_backup')->nullable();
            \$table->timestamp('last_check_at')->nullable();"

# 9. customers
create_migration "customers" "            \$table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            \$table->string('name');
            \$table->string('email')->nullable();
            \$table->string('phone');
            \$table->text('address');
            \$table->string('id_card_number')->nullable();
            \$table->enum('status', ['active', 'inactive', 'suspended'])->default('active');"

# 10. subscriptions
create_migration "subscriptions" "            \$table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            \$table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            \$table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            \$table->date('start_date');
            \$table->date('end_date')->nullable();
            \$table->decimal('price', 10, 2);
            \$table->decimal('discount', 10, 2)->default(0);
            \$table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');"

# 11. provisionings
create_migration "provisionings" "            \$table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            \$table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            \$table->string('pppoe_username')->unique();
            \$table->string('pppoe_password');
            \$table->ipAddress('ip_address')->nullable();
            \$table->string('queue_name')->nullable();
            \$table->enum('status', ['active', 'soft_limited', 'suspended', 'terminated'])->default('active');
            \$table->timestamp('last_ping_at')->nullable();
            \$table->integer('ping_latency')->nullable();"

# 12. invoices
create_migration "invoices" "            \$table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            \$table->string('invoice_number')->unique();
            \$table->string('period');
            \$table->decimal('subtotal', 10, 2);
            \$table->decimal('discount', 10, 2)->default(0);
            \$table->decimal('tax', 10, 2)->default(0);
            \$table->decimal('total', 10, 2);
            \$table->date('due_date');
            \$table->enum('status', ['unpaid', 'paid', 'cancelled', 'refunded'])->default('unpaid');
            \$table->timestamp('paid_at')->nullable();"

# 13. invoice_items
create_migration "invoice_items" "            \$table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            \$table->string('description');
            \$table->integer('quantity')->default(1);
            \$table->decimal('unit_price', 10, 2);
            \$table->decimal('total', 10, 2);"

# 14. payments
create_migration "payments" "            \$table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            \$table->string('payment_number')->unique();
            \$table->decimal('amount', 10, 2);
            \$table->string('payment_method');
            \$table->string('payment_gateway')->nullable();
            \$table->string('transaction_id')->nullable();
            \$table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            \$table->text('notes')->nullable();
            \$table->timestamp('paid_at')->nullable();"

# 15. templates
create_migration "templates" "            \$table->string('name');
            \$table->enum('type', ['email', 'sms', 'whatsapp']);
            \$table->enum('category', ['invoice', 'reminder', 'notification', 'marketing']);
            \$table->string('subject')->nullable();
            \$table->text('content');
            \$table->json('variables')->nullable();
            \$table->boolean('is_active')->default(true);"

# 16. reminders
create_migration "reminders" "            \$table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            \$table->enum('type', ['email', 'sms', 'whatsapp']);
            \$table->enum('stage', ['h_minus_7', 'h_minus_3', 'h_minus_1', 'h_plus_1', 'pre_soft_limit', 'pre_suspend']);
            \$table->timestamp('sent_at');
            \$table->enum('status', ['sent', 'failed'])->default('sent');
            \$table->text('error_message')->nullable();
            \$table->string('idempotency_key')->unique();"

# 17. tickets
create_migration "tickets" "            \$table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            \$table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            \$table->string('ticket_number')->unique();
            \$table->string('subject');
            \$table->text('description');
            \$table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            \$table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            \$table->timestamp('resolved_at')->nullable();"

# 18. audit_logs
create_migration "audit_logs" "            \$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            \$table->string('action');
            \$table->string('model_type')->nullable();
            \$table->unsignedBigInteger('model_id')->nullable();
            \$table->json('old_values')->nullable();
            \$table->json('new_values')->nullable();
            \$table->ipAddress('ip_address')->nullable();
            \$table->string('user_agent')->nullable();
            
            \$table->index(['model_type', 'model_id']);
            \$table->index('action');"

echo ""
echo "✅ All migrations created successfully!"
echo ""
echo "Next steps:"
echo "1. cd apps/api"
echo "2. php artisan migrate"
