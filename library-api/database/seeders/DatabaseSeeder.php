<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Default
        User::create([
            'name'     => 'Pustakawan Admin',
            'email'    => 'admin@perpustakaan.com',
            'password' => bcrypt('admin123'),
            'role'     => 'admin',
        ]);

        // Member Default
        User::create([
            'name'     => 'Ahmad Member',
            'email'    => 'member@perpustakaan.com',
            'password' => bcrypt('member123'),
            'role'     => 'member',
        ]);

        // Sample Books
        \App\Models\Book::create([
            'title'          => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'author'         => 'Robert C. Martin',
            'publisher'      => 'Prentice Hall',
            'isbn'           => '978-0132350884',
            'category'       => 'Teknologi',
            'stock'          => 5,
            'shelf_location' => 'Rak A-01',
        ]);

        \App\Models\Book::create([
            'title'          => 'The Pragmatic Programmer: Your Journey To Mastery',
            'author'         => 'David Thomas, Andrew Hunt',
            'publisher'      => 'Addison-Wesley',
            'isbn'           => '978-0135957059',
            'category'       => 'Teknologi',
            'stock'          => 3,
            'shelf_location' => 'Rak A-02',
        ]);

        \App\Models\Book::create([
            'title'          => 'Refactoring: Improving the Design of Existing Code',
            'author'         => 'Martin Fowler',
            'publisher'      => 'Addison-Wesley Professional',
            'isbn'           => '978-0201485677',
            'category'       => 'Teknologi',
            'stock'          => 2,
            'shelf_location' => 'Rak A-03',
        ]);

        \App\Models\Book::create([
            'title'          => 'Laskar Pelangi',
            'author'         => 'Andrea Hirata',
            'publisher'      => 'Bentang Pustaka',
            'isbn'           => '978-9791227186',
            'category'       => 'Fiksi / Sastra',
            'stock'          => 4,
            'shelf_location' => 'Rak B-01',
        ]);

        \App\Models\Book::create([
            'title'          => 'Atomic Habits',
            'author'         => 'James Clear',
            'publisher'      => 'Gramedia Pustaka Utama',
            'isbn'           => '978-6020633176',
            'category'       => 'Pengembangan Diri',
            'stock'          => 0, // Sengaja 0 untuk demo stok habis
            'shelf_location' => 'Rak C-05',
        ]);
    }
}
