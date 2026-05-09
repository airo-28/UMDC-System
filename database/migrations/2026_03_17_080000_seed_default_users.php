<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;

return new class extends Migration
{
    /**
     * Seed default users via migration so they exist on production.
     * Idempotent: only inserts if the email doesn't already exist.
     */
    public function up(): void
    {
        $users = [
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'email'      => 'yamikaaaa@gmail.com',
                'role'       => 'admin',
                'password'   => 'admin123',
                'is_active'  => true,
            ],
            [
                'first_name' => 'Cashier',
                'last_name'  => 'Staff',
                'email'      => 'cashier@udc.com',
                'role'       => 'cashier',
                'password'   => 'cashier123',
                'is_active'  => true,
            ],
            [
                'first_name' => 'Kitchen',
                'last_name'  => 'Manager',
                'email'      => 'kitchen@udc.com',
                'role'       => 'kitchen_manager',
                'password'   => 'kitchen123',
                'is_active'  => true,
            ],
            [
                'first_name' => 'Inventory',
                'last_name'  => 'Manager',
                'email'      => 'inventory@udc.com',
                'role'       => 'inventory_manager',
                'password'   => 'inventory123',
                'is_active'  => true,
            ],
        ];

        foreach ($users as $userData) {
            if (!User::where('email', $userData['email'])->exists()) {
                User::create($userData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::whereIn('email', [
            'admin@udc.com',
            'cashier@udc.com',
            'kitchen@udc.com',
            'inventory@udc.com',
        ])->delete();
    }
};
