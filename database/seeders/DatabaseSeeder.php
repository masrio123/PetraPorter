<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Porter;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\BankUser;
use App\Models\Category;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Department;
use App\Models\OrderDetail;
use App\Models\OrderHistory;
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

        foreach ($customerNames as $key => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'customer' . $key . '@gmail.com',
                'password' => Hash::make('customer123'),
            ]);

            $user->assignRole("customer");

            \App\Models\Customer::create([
                'customer_name' => $name,
                'department_id' => \App\Models\Department::inRandomOrder()->first()->id,
                'bank_user_id' => \App\Models\BankUser::inRandomOrder()->first()->id,
                'user_id' => $user->id
            ]);
        }

        // Seed Porters berdasarkan BankUser
        $bankUsers = \App\Models\BankUser::all();

        foreach ($bankUsers as $index => $bankUser) {
            $user = User::create([
                'name' => $bankUser->username,
                'email' => 'porter' . $index . '@gmail.com',
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
            'on-delivery',
            'finished',
            'canceled',
            'waiting',
        ];

        foreach ($statuses as $status) {
            DB::table('order_statuses')->insert([
                'order_status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed untuk dummy order
       for ($i = 0; $i < 20; $i++) {
    $customer = Customer::inRandomOrder()->first();
    $tenant = Tenant::inRandomOrder()->first();
    $products = Product::where('tenant_id', $tenant->id)->inRandomOrder()->take(rand(1, 3))->get();
    $deliveryPoint = DeliveryPoint::inRandomOrder()->first();

    $shipping_cost = 10000;
    $total_price = 0;

    // Hitung total_price berdasarkan subtotal semua product
    $productQtyMap = [];
    foreach ($products as $product) {
        $qty = rand(1, 3);
        $total_price += $product->price * $qty;
        $productQtyMap[] = ['product' => $product, 'qty' => $qty];
    }

    // Buat atau ambil cart berdasarkan kombinasi customer & tenant_location
    $cart = Cart::firstOrCreate([
        'customer_id' => $customer->id,
        'tenant_location_id' => $tenant->tenant_location_id,
    ]);

    // Buat order
    $order = Order::create([
        'cart_id' => $cart->id,
        'customer_id' => $customer->id,
        'tenant_location_id' => $tenant->tenant_location_id,
        'order_status_id' => DB::table('order_statuses')->inRandomOrder()->first()->id,
        'total_price' => $total_price,
        'shipping_cost' => $shipping_cost,
        'grand_total' => $total_price + $shipping_cost,
        'porter_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Buat order item
    foreach ($productQtyMap as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product']->id,
            'tenant_id' => $tenant->id,
            'quantity' => $item['qty'],
            'price' => $item['product']->price,
            'subtotal' => $item['product']->price * $item['qty'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
    }
}
