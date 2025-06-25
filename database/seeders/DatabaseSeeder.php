<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Porter;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Department;
use App\Models\DeliveryPoint;
use App\Models\TenantLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'tenant']);
        Role::create(['name' => 'porter']);
        Role::create(['name' => 'customer']);

        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $admin->assignRole('admin');

        // Seed Tenant Locations
        $locations = ["P", "Q", "W", "T"];
        foreach ($locations as $location) {
            TenantLocation::create([
                "location_name" => 'Gedung ' . $location
            ]);
        }

        //Seed Tenant
        // Buat kategori (sekali saja)
        $kategoriMakanan = Category::create(['category_name' => 'Makanan']);
        $kategoriMinuman = Category::create(['category_name' => 'Minuman']);

        // Ambil lokasi gedung (P, Q, W, T)
        $lokasiGedung = TenantLocation::whereIn('location_name', ['Gedung P', 'Gedung Q', 'Gedung W', 'Gedung T'])->get()->keyBy('location_name');

        // Data tenant dibagi per gedung
        $tenantData = [
            [
                'name' => 'Kobakso',
                'gedung' => 'Gedung P',
                'menus' => [
                    ['name' => 'Mie Ayam Bakso', 'category' => $kategoriMakanan],
                    ['name' => 'Bakso Urat Pedas', 'category' => $kategoriMakanan],
                    ['name' => 'Es Teh Manis', 'category' => $kategoriMinuman],
                    ['name' => 'Es Jeruk', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Bakpao Gracias',
                'gedung' => 'Gedung P',
                'menus' => [
                    ['name' => 'Bakpao Coklat', 'category' => $kategoriMakanan],
                    ['name' => 'Bakpao Ayam', 'category' => $kategoriMakanan],
                    ['name' => 'Susu Kedelai', 'category' => $kategoriMinuman],
                    ['name' => 'Teh Hangat', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Bakso Petra',
                'gedung' => 'Gedung Q',
                'menus' => [
                    ['name' => 'Bakso Petra Spesial', 'category' => $kategoriMakanan],
                    ['name' => 'Bakso Biasa Petra', 'category' => $kategoriMakanan],
                    ['name' => 'Es Campur', 'category' => $kategoriMinuman],
                    ['name' => 'Teh Tawar', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Ndokee Express',
                'gedung' => 'Gedung Q',
                'menus' => [
                    ['name' => 'Mie Goreng Ndokee', 'category' => $kategoriMakanan],
                    ['name' => 'Mie Kuah Pedas', 'category' => $kategoriMakanan],
                    ['name' => 'Thai Tea', 'category' => $kategoriMinuman],
                    ['name' => 'Kopi Susu', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Depot Mapan',
                'gedung' => 'Gedung W',
                'menus' => [
                    ['name' => 'Nasi Campur Komplit', 'category' => $kategoriMakanan],
                    ['name' => 'Nasi Ayam Kremes', 'category' => $kategoriMakanan],
                    ['name' => 'Es Degan', 'category' => $kategoriMinuman],
                    ['name' => 'Teh Botol', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Tong Tji',
                'gedung' => 'Gedung W',
                'menus' => [
                    ['name' => 'Teh Hijau Original', 'category' => $kategoriMinuman],
                    ['name' => 'Teh Tarik', 'category' => $kategoriMinuman],
                    ['name' => 'Teh Leci', 'category' => $kategoriMinuman],
                    ['name' => 'Pisang Goreng', 'category' => $kategoriMakanan],
                ],
            ],
            [
                'name' => 'Pangsit Mie Bu Kusni',
                'gedung' => 'Gedung T',
                'menus' => [
                    ['name' => 'Pangsit Mie Komplit', 'category' => $kategoriMakanan],
                    ['name' => 'Mie Ayam Ceker', 'category' => $kategoriMakanan],
                    ['name' => 'Cincau Susu', 'category' => $kategoriMinuman],
                    ['name' => 'Lemon Tea', 'category' => $kategoriMinuman],
                ],
            ],
            [
                'name' => 'Mie Pinangsia Aboen',
                'gedung' => 'Gedung T',
                'menus' => [
                    ['name' => 'Mie Pinangsia Spesial', 'category' => $kategoriMakanan],
                    ['name' => 'Bakso Ikan Pinangsia', 'category' => $kategoriMakanan],
                    ['name' => 'Susu Jahe', 'category' => $kategoriMinuman],
                    ['name' => 'Air Mineral', 'category' => $kategoriMinuman],
                ],
            ],
        ];

        // Proses buat tenant, user, menu
        foreach ($tenantData as $index => $tenantInfo) {
            $user = User::create([
                'name' => 'Tenant ' . $tenantInfo['name'],
                'email' => 'tenant' . $index . '@gmail.com',
                'password' => Hash::make('tenant123'),
            ]);

            $user->assignRole('tenant');

            $tenant = Tenant::create([
                'name' => $tenantInfo['name'],
                'tenant_location_id' => $lokasiGedung[$tenantInfo['gedung']]->id,
                'user_id' => $user->id,
                'isOpen' => true,
            ]);

            foreach ($tenantInfo['menus'] as $menu) {
                Product::create([
                    'name' => $menu['name'],
                    'price' => rand(8000, 30000),
                    'tenant_id' => $tenant->id,
                    'category_id' => $menu['category']->id,
                ]);
            }
        }

        // Seed Departments
        $departments = [
            'Informatika',
            'SIB', // Sistem Informasi Bisnis
            'DSA', // Data Science and Analytics
        ];
        foreach ($departments as $dept) {
            Department::create(['department_name' => $dept]);
        }

        // Data Bank
        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Danamon', 'PermataBank', 'OCBC NISP', 'Panin Bank', 'BTN'];


        // Seed Delivery Points
        $deliveryPoints = [
            'Selasar Gedung P',
            'Ruang Dosen Gedung P',
            'Laboratorium Gedung P',
            'Laboratorium Gedung W',
            'Ruang Rektorat',
            'Ruang Rapat Gedung W',
            'Laboratorium Gedung T',
            'Ruang Dosen Gedung T',
            'Laboratorium Gedung Q',
            'Auditorium Gedung Q',
            'Skyfit Gym',
            'Ruang Dosen Gedung Q',
            'Selasar Gedung Q',
        ];
        foreach ($deliveryPoints as $point) {
            DeliveryPoint::create([
                'delivery_point_name' => $point,
            ]);
        }
        $nrpCounter = 1;

        // 3. Seed Customers
        $customerNames = [
            'Reyhan Renjiro Lauwrens',
            'Gabrielle Abraham',
            'Theodore Eldwin',
            'Ignatius Jonathan',
            'Satriya Handha',
            'Jane Iolana',
            'Calvin Wijaya',
            'Samantha Chen',
            'Stanley Hadikusuma',
        ];

        foreach ($customerNames as $name) {
            $nrp = 'c1421' . str_pad($nrpCounter, 4, '0', STR_PAD_LEFT);

            $user = User::create([
                'name'     => $name,
                'email'    => $nrp . '@john.petra.ac.id',
                'password' => Hash::make('customer123'),
            ]);
            $user->assignRole("customer");

            Customer::create([
                'customer_name'   => $name,
                'identity_number' => $nrp,
                'department_id'   => Department::inRandomOrder()->first()->id,
                'bank_name'       => $banks[array_rand($banks)],
                'account_numbers' => (string) mt_rand(1000000000, 9999999999),
                'username'        => $name,
                'user_id'         => $user->id,
            ]);
            $nrpCounter++;
        }

        $statuses = [
            'Received',
            'On-Delivery',
            'Finished',
            'Canceled',
            'Waiting',
        ];

        foreach ($statuses as $status) {
            DB::table('order_statuses')->insert([
                'order_status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
