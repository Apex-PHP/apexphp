<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Admin',
                'email' => 'admin@email.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'João Silva',
                'email' => 'joao@email.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@email.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('users')->insert($data)->saveData();
    }
}
