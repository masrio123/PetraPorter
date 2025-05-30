<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\User;
use App\Models\Porter;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\BankUser;
use App\Models\Category;
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
        Role::create(['name' => 'user']);

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

        // Seed Tenants
        $tenants = [
            "Kobakso",
            "Bakpao Gracias",
            "Bakso Petra",
            "Ndokee Express",
            "Ndokee Express",
            "Depot Mapan",
            "Pangsit Mie Bu Kusni",
            "Tong Tji",
            "Mie Pinangsia Aboen",
            "Ndokee Express",
            "Pangsit Mie Tenda Biru",
            "Singapore Crispy Snacks"
        ];
        foreach ($tenants as $key => $tenant) {
            $user = User::create([
                'name' => 'Tenant '. $tenant,
                'email' => 'tenant'.$key.'@gmail.com',
                'password' => Hash::make('tenant123'), 
            ]);

            $user->assignRole("tenant");

            Tenant::create([
                "name" => $tenant,
                'tenant_location_id' => TenantLocation::inRandomOrder()->first()->id,
                'user_id' => $user->id,
                'isOpen' => true
            ]);
        }

        // Seed Categories & Products
        $categories = [
            [
                'name' => 'Makanan',
                'menus' => [
                    'Ayam Geprek',
                    'Seblak Jeletet',
                    'Mie Setan',
                    'Sambal Bakar Spesial',
                    'Tahu Crispy',
                    'Cireng Isi',
                    'Kentang Goreng',
                    'Bakwan Sayur',
                    'Nasi Goreng Spesial',
                    'Sate Ayam',
                    'Rendang Daging',
                    'Ayam Bakar Madu',
                    'Spaghetti Bolognese',
                    'Sushi Roll',
                    'Burger Daging Sapi',
                    'Pizza Keju',
                    'Gudeg Jogja',
                    'Pempek Palembang',
                    'Lontong Sayur',
                    'Rawon Surabaya',
                    'Pisang Coklat',
                    'Kue Cubit',
                    'Martabak Manis',
                    'Pudding Coklat',
                    'Lemper Ayam',
                    'Nagasari',
                    'Klepon',
                    'Pastel Goreng',
                    'Mie Ayam Komplit',
                    'Mie Goreng Jawa',
                    'Mie Kocok Bandung',
                    'Ramen Pedas',
                    'Nasi Uduk',
                    'Nasi Kuning',
                    'Nasi Liwet',
                    'Nasi Campur Bali',
                    'Ikan Bakar Rica',
                    'Cumi Goreng Tepung',
                    'Udang Saus Padang',
                    'Kerang Rebus',
                    'Gado-Gado',
                    'Tumis Kangkung',
                    'Sayur Lodeh',
                    'Tahu Tempe Bacem'
                ]
            ],
            [
                'name' => 'Minuman',
                'menus' => [
                    'Es Teh Manis',
                    'Es Kopi Susu',
                    'Thai Tea',
                    'Es Cincau',
                    'Teh Tawar Hangat',
                    'Kopi Tubruk',
                    'Wedang Jahe',
                    'Coklat Panas',
                    'Bajigur',
                    'Bandrek',
                    'Wedang Uwuh',
                    'Cendol Dawet',
                    'Boba Milk Tea',
                    'Kopi Susu Gula Aren',
                    'Mojito Lemon',
                    'Yakult Green Tea'
                ]
            ]
        ];

        foreach ($categories as $category) {
            $cat = Category::create([
                'category_name' => $category['name'],
            ]);
            $tenant_id = Tenant::inRandomOrder()->first()->id;
            foreach ($category['menus'] as $menu) {
                Product::create([
                    'name' => $menu,
                    'price' => rand(5000, 50000),
                    'tenant_id' => $tenant_id,
                    'category_id' => $cat->id,
                ]);
            }
        }

        // Seed Departments
        $departments = [
            'Informatika',
            'Sistem Informasi',
            'Teknik Sipil',
            'Arsitektur',
            'Manajemen',
            'Akuntansi',
            'Desain Komunikasi Visual',
            'Ilmu Komunikasi',
            'Sastra Inggris',
            'Hukum',
            'Teknik Elektro',
            'Teknik Mesin',
            'Teknik Industri',
            'Psikologi',
            'Pendidikan Bahasa Inggris',
            'Bioteknologi',
            'Matematika',
            'Statistika',
            'Kedokteran',
            'Farmasi'
        ];
        foreach ($departments as $dept) {
            Department::create([
                'department_name' => $dept
            ]);
        }

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

        // Seed Banks
        $banks = [
            'Bank Central Asia (BCA)',
            'Bank Mandiri',
            'Bank Rakyat Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Syariah Indonesia (BSI)'
        ];
        foreach ($banks as $bankName) {
            Bank::create(['bank_name' => $bankName]);
        }
        // Seed Bank Users
        $bankUsers = [
            ['username' => 'Andi Wijaya',     'account_number' => '1234567890'],
            ['username' => 'Siti Nurhaliza',  'account_number' => '2345678901'],
            ['username' => 'Budi Santoso',    'account_number' => '3456789012'],
            ['username' => 'Dewi Lestari',    'account_number' => '4567890123'],
            ['username' => 'Agus Prabowo',    'account_number' => '5678901234'],
            ['username' => 'Rina Marlina',    'account_number' => '6789012345'],
            ['username' => 'Tono Suhendra',   'account_number' => '7890123456'],
            ['username' => 'Wulan Ayu',       'account_number' => '8901234567'],
            ['username' => 'Fajar Nugroho',   'account_number' => '9012345678'],
            ['username' => 'Melati Putri',    'account_number' => '0123456789'],
        ];

        foreach ($bankUsers as $user) {
            BankUser::create([
                'username' => $user['username'],
                'account_number' => $user['account_number'],
                'bank_id' => Bank::inRandomOrder()->first()->id,
            ]);
        }

        // Seed Customers
        $customerNames = [
            'Daniel Simanjuntak',
            'Yuliana Sari',
            'Rendy Mahardika',
            'Clara Wibowo',
            'Iman Firmansyah',
            'Tania Lestari',
            'Hendrik Gunawan',
            'Maria Kristina',
            'Alvin Nugroho',
            'Vania Yosephine',
        ];

        foreach ($customerNames as $name) {
            \App\Models\Customer::create([
                'customer_name' => $name,
                'department_id' => \App\Models\Department::inRandomOrder()->first()->id,
                'bank_user_id' => \App\Models\BankUser::inRandomOrder()->first()->id,
            ]);
        }

        // Seed Porters berdasarkan BankUser
        $bankUsers = \App\Models\BankUser::all();

        foreach ($bankUsers as $index => $bankUser) {
            $user = User::create([
                'name' => $bankUser->username,
                'email' => 'porter'.$index.'@gmail.com',
                'password' => Hash::make('porter123'), 
            ]);

            $user->assignRole("porter");

            \App\Models\Porter::create([
                'porter_name'     => $bankUser->username,
                'porter_nrp'      => '24010' . str_pad($index + 1, 3, '0', STR_PAD_LEFT), // Contoh NRP: 24010001 dst
                'department_id'   => \App\Models\Department::inRandomOrder()->first()->id,
                'bank_user_id'    => $bankUser->id,
                'porter_isOnline' => false,
                'user_id' => $user->id
            ]);
        }

         $statuses = [
            'received',
            'processing',
            'picked_up',
            'delivered',
            'finished',
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
