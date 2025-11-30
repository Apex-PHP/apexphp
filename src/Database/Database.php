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

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            self::$instance = $capsule;
        }

        return self::$instance;
    }
}
