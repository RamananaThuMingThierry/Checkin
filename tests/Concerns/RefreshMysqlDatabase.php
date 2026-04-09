<?php

namespace Tests\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDO;

trait RefreshMysqlDatabase
{
    public function setUpRefreshMysqlDatabase(): void
    {
        if (Config::get('database.default') !== 'mysql') {
            return;
        }

        $connection = Config::get('database.connections.mysql');
        $database = sprintf(
            '%s_%s',
            $connection['database'],
            substr(md5(static::class.'::'.$this->name().microtime(true)), 0, 10)
        );
        $charset = $connection['charset'] ?? 'utf8mb4';
        $collation = $connection['collation'] ?? 'utf8mb4_general_ci';

        DB::purge('mysql');

        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;charset=%s', $connection['host'], $connection['port'], $charset),
            $connection['username'],
            $connection['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");

        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');

        $this->artisan('migrate', ['--force' => true]);

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () use ($connection, $database, $charset): void {
            DB::purge('mysql');

            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;charset=%s', $connection['host'], $connection['port'], $charset),
                $connection['username'],
                $connection['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
        });
    }
}
