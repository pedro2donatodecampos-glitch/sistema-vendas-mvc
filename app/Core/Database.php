<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Padrão Singleton: garante uma única conexão com o banco SQLite por requisição.
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $storage = dirname(__DIR__, 2) . '/storage';
            if (!is_dir($storage) && !mkdir($storage, 0775, true) && !is_dir($storage)) {
                throw new \RuntimeException('Não foi possível criar o diretório de armazenamento.');
            }

            self::$connection = new PDO('sqlite:' . $storage . '/database.sqlite');
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$connection->exec('PRAGMA foreign_keys = ON');
            self::migrate(self::$connection);
        }

        return self::$connection;
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT,
                phone TEXT,
                document TEXT,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
                unit_price REAL NOT NULL CHECK (unit_price >= 0),
                package_price REAL NOT NULL DEFAULT 0 CHECK (package_price >= 0),
                image TEXT,
                expires_at TEXT
            );
            CREATE TABLE IF NOT EXISTS sales (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER,
                total REAL NOT NULL CHECK (total >= 0),
                payment_method TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT "completed",
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
            );
            CREATE TABLE IF NOT EXISTS sale_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sale_id INTEGER NOT NULL,
                product_id INTEGER,
                product_name TEXT NOT NULL,
                quantity INTEGER NOT NULL CHECK (quantity > 0),
                unit_price REAL NOT NULL CHECK (unit_price >= 0),
                FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )'
        );

        $columns = array_column($pdo->query('PRAGMA table_info(products)')->fetchAll(), 'name');
        if (!in_array('expires_at', $columns, true)) {
            $pdo->exec('ALTER TABLE products ADD COLUMN expires_at TEXT');
        }

        $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        if ($count === 0) {
            $insert = $pdo->prepare(
                'INSERT INTO products (code, name, category, stock, unit_price, package_price, image, expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ([
                ['PER-001', 'Far Away Glamour', 'Perfumaria', 12, 189.90, 0, '', '2028-12-31'],
                ['PER-002', 'Essencial Feminino', 'Perfumaria', 10, 159.90, 0, '', '2028-06-30'],
                ['HID-001', 'Loção Corporal Encanto', 'Cuidados pessoais', 18, 49.90, 0, '', '2027-10-31'],
            ] as $product) {
                $insert->execute($product);
            }
        }
    }
}
