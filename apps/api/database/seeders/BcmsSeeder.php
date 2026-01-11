<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BcmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users Groups
        $adminGroup = DB::table('users_groups')->insertGetId([
            'name' => 'Administrator',
            'permissions' => json_encode([
                'users' => ['create', 'read', 'update', 'delete'],
                'customers' => ['create', 'read', 'update', 'delete'],
                'invoices' => ['create', 'read', 'update', 'delete'],
                'products' => ['create', 'read', 'update', 'delete'],
                'routers' => ['create', 'read', 'update', 'delete'],
                'tickets' => ['create', 'read', 'update', 'delete'],
                'reports' => ['read', 'export'],
            ]),
            'description' => 'Full system access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supervisorGroup = DB::table('users_groups')->insertGetId([
            'name' => 'Supervisor',
            'permissions' => json_encode([
                'customers' => ['create', 'read', 'update'],
                'invoices' => ['read', 'update'],
                'products' => ['read'],
                'tickets' => ['create', 'read', 'update', 'delete'],
                'reports' => ['read'],
            ]),
            'description' => 'Supervisor access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $financeGroup = DB::table('users_groups')->insertGetId([
            'name' => 'Finance/Kasir',
            'permissions' => json_encode([
                'customers' => ['read'],
                'invoices' => ['create', 'read', 'update'],
                'payments' => ['create', 'read', 'update'],
                'reports' => ['read', 'export'],
            ]),
            'description' => 'Finance and payment access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supportGroup = DB::table('users_groups')->insertGetId([
            'name' => 'Support',
            'permissions' => json_encode([
                'customers' => ['read', 'update'],
                'tickets' => ['create', 'read', 'update'],
            ]),
            'description' => 'Customer support access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nocGroup = DB::table('users_groups')->insertGetId([
            'name' => 'NOC/Technician',
            'permissions' => json_encode([
                'customers' => ['read'],
                'routers' => ['read', 'update'],
                'provisionings' => ['create', 'read', 'update'],
                'tickets' => ['read', 'update'],
            ]),
            'description' => 'Network operations access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Users
        DB::table('users')->insert([
            [
                'users_group_id' => $adminGroup,
                'name' => 'Abramz',
                'email' => 'abramz@bcms.com',
                'password' => Hash::make('password123'),
                'phone' => '08123456789',
                'address' => 'Jakarta, Indonesia',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'users_group_id' => $supervisorGroup,
                'name' => 'Fandi',
                'email' => 'fandi@bcms.com',
                'password' => Hash::make('password123'),
                'phone' => '08123456790',
                'address' => 'Bandung, Indonesia',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'users_group_id' => $financeGroup,
                'name' => 'Meci',
                'email' => 'meci@bcms.com',
                'password' => Hash::make('password123'),
                'phone' => '08123456791',
                'address' => 'Surabaya, Indonesia',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'users_group_id' => $supportGroup,
                'name' => 'Yogi',
                'email' => 'yogi@bcms.com',
                'password' => Hash::make('password123'),
                'phone' => '08123456792',
                'address' => 'Yogyakarta, Indonesia',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Company
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'PT. Trira Inti Utama',
            'address' => 'Jakarta, Indonesia',
            'phone' => '021-12345678',
            'email' => 'info@trirainti.com',
            'bank_account' => json_encode([
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1234567890',
                'account_holder' => 'PT. Trira Inti Utama',
            ]),
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Brand
        $brandId = DB::table('brands')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Maroon-NET',
            'slug' => 'maroon-net',
            'description' => 'Internet Service Provider',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Products
        $products = [
            ['name' => 'Home 10 Mbps', 'price' => 200000, 'down' => 10000, 'up' => 10000],
            ['name' => 'Home 20 Mbps', 'price' => 300000, 'down' => 20000, 'up' => 20000],
            ['name' => 'Business 50 Mbps', 'price' => 750000, 'down' => 50000, 'up' => 50000],
        ];

        foreach ($products as $product) {
            $productId = DB::table('products')->insertGetId([
                'brand_id' => $brandId,
                'name' => $product['name'],
                'description' => 'Paket internet ' . $product['name'],
                'price' => $product['price'],
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('internet_services')->insert([
                'product_id' => $productId,
                'bandwidth_download' => $product['down'],
                'bandwidth_upload' => $product['up'],
                'auto_soft_limit' => 5,
                'auto_suspend' => 7,
                'quota_limit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Routers (offline initially)
        DB::table('routers')->insert([
            'name' => 'Router Pusat',
            'ip_address' => '192.168.1.1',
            'api_port' => 8729,
            'ssh_port' => 22,
            'username' => 'admin',
            'password' => Hash::make('routerpass'),
            'status' => 'offline',
            'config_backup' => null,
            'last_check_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ BCMS database seeded successfully!\n";
        echo "Login credentials:\n";
        echo "  Admin: abramz@bcms.com / password123\n";
        echo "  Supervisor: fandi@bcms.com / password123\n";
        echo "  Finance: meci@bcms.com / password123\n";
        echo "  Support: yogi@bcms.com / password123\n";
    }
}
