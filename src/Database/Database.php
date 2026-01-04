<?php

namespace Framework\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    private static ?Capsule $instance = null;

    public static function getInstance(): Capsule
    {
        if (self::$instance === null) {
            $capsule = new Capsule;

            $config = config('database.connections.' . config('database.default'));


            if ($config['driver'] === 'sqlite') {
                $capsule->addConnection([
                    'driver' => $config['driver'],
                    'database' => __DIR__ . "/../../storage/" . $config['database'],
                    'prefix' => $config['prefix'],
                    // Opcional - caso queira ativar foreign keys no SQLite
                    'foreign_key_constraints' => true,
                ]);
            } else if ($config['driver'] === 'mysql') {
                $capsule->addConnection([
                    'driver' => $config['driver'],
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'database' => $config['database'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'charset' => $config['charset'],
                    'collation' => $config['collation'],
                    'prefix' => $config['prefix'],
                ]);
            } else if ($config['driver'] === 'pgsql') {
                $capsule->addConnection([
                    'driver' => $config['driver'],
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'database' => $config['database'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'charset' => $config['charset'],
                    'prefix' => $config['prefix'],
                    'schema' => $config['schema'],          // schema padrão do PostgreSQL
                    'sslmode' => $config['sslmode'],        // 'disable', 'allow', 'prefer', 'require', etc
                ]);
            } else {
                throw new \Exception("Unsupported database driver: " . $config['driver']);
            }

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            self::$instance = $capsule;
        }

        return self::$instance;
    }
}
