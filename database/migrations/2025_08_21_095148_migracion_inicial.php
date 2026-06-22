<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigracionInicial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $path = database_path('scripts/inicio.sql');

        if (! File::exists($path)) {
            throw new RuntimeException("No se encontró el archivo SQL en: {$path}");
        }

        $sql = File::get($path);

        // Limpieza más agresiva de encabezados de phpMyAdmin y comandos no necesarios
        $removePatterns = [
            '/^--.*$/m',                         // comentarios -- ...
            '/\/\*![0-9]+\s.*?\*\//s',          // bloques /*!40101 SET ... */
            '/\bSET\s+SQL_MODE\s*=.*?;?/i',     // SET SQL_MODE = ...
            '/\bSET\s+time_zone\s*=.*?;?/i',    // SET time_zone = ...
            '/\bSTART\s+TRANSACTION;?/i',        // START TRANSACTION
            '/\bCOMMIT;?/i',                     // COMMIT
            '/\bSET\s+@OLD_CHARACTER_SET_CLIENT.*?;?/i',  // SET @OLD_CHARACTER_SET_CLIENT
            '/\bSET\s+@OLD_CHARACTER_SET_RESULTS.*?;?/i', // SET @OLD_CHARACTER_SET_RESULTS
            '/\bSET\s+@OLD_COLLATION_CONNECTION.*?;?/i',  // SET @OLD_COLLATION_CONNECTION
            '/\bSET\s+NAMES\s+.*?;?/i',         // SET NAMES utf8mb4
            '/^\/\*.*?\*\/\s*$/s',              // comentarios /* ... */
        ];
        $sql = preg_replace($removePatterns, '', $sql);

        // Apagar FKs para crear tablas e índices sin orden estricto
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Partir por ; al final de línea y filtrar mejor
        $statements = preg_split('/;\s*(\r?\n)/', $sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            
            // Filtrar declaraciones vacías o problemáticas
            if ($stmt === '' || 
                strtoupper($stmt) === 'COMMIT' ||
                strtoupper($stmt) === 'START TRANSACTION' ||
                preg_match('/^SET\s+/i', $stmt) ||
                preg_match('/^\/\*.*\*\/$/s', $stmt)) {
                continue;
            }
            
            // Solo ejecutar CREATE TABLE, ALTER TABLE, INSERT, etc.
            if (preg_match('/^(CREATE|ALTER|INSERT|UPDATE|DELETE|DROP|INDEX|KEY|CONSTRAINT)/i', $stmt)) {
                try {
                    DB::unprepared($stmt);
                } catch (Exception $e) {
                    // Log del error pero continuar con la migración
                    Log::warning("Error ejecutando SQL en migración: " . $e->getMessage());
                    Log::warning("SQL problemático: " . substr($stmt, 0, 100) . "...");
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
