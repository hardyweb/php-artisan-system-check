<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class SystemCheckCommand extends Command
{
    protected $signature = 'system:check {--fix : Auto fix issues}';
    protected $description = 'Check system setup and dependencies';
    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('       System Setup Check');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        // ============================================================
        // ADJUST THESE SECTIONS BASED ON YOUR PROJECT NEEDS
        // ============================================================
        $this->section('PHP Environment');
        if ($this->checkPhp()) {
            $passed++;
        } else {
            $failed++;
        }
        $this->section('Database');
        if ($this->checkDatabase()) {
            $passed++;
        } else {
            $failed++;
        }
        $this->section('Storage');
        if ($this->checkStorage()) {
            $passed++;
        } else {
            $failed++;
        }
        // Optional: Add more sections as needed
        // $this->section('Redis');
        // if ($this->checkRedis()) { $passed++; } else { $warnings++; }
        // $this->section('Mail');
        // if ($this->checkMail()) { $passed++; } else { $warnings++; }
        // ============================================================
        // END ADJUSTABLE SECTIONS
        // ============================================================
        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("  Summary: {$passed} passed, {$warnings} warnings, {$failed} failed");
        $this->info('═══════════════════════════════════════════════════════════');
        if ($failed > 0) {
            $this->newLine();
            $this->error('❌ Some checks failed! Please fix the issues above.');
            return Command::FAILURE;
        }
        if ($warnings > 0) {
            $this->newLine();
            $this->warn('⚠️  System works but some features may not be available.');
            return Command::SUCCESS;
        }
        $this->newLine();
        $this->info('✅ All checks passed! System is ready to use.');
        return Command::SUCCESS;
    }
    protected function section(string $title): void
    {
        $this->newLine();
        $this->info("▸ {$title}");
        $this->line(str_repeat('─', 60));
    }
    // ============================================================
    // DEFAULT CHECKS - Adjust as needed
    // ============================================================
    /**
     * Check PHP version and required extensions
     */
    protected function checkPhp(): bool
    {
        // ADJUST: Minimum PHP version
        $minVersion = '8.2.0';
        if (version_compare(PHP_VERSION, $minVersion, '>=')) {
            $this->line('  PHP Version: ' . PHP_VERSION . ' ✅');
            // ADJUST: Required extensions for your project
            $extensions = [
                'pdo',
                'mbstring',
                'openssl',
                'curl',
                'zip',
                'gd',
            ];
            $missing = [];
            foreach ($extensions as $ext) {
                if (! extension_loaded($ext)) {
                    $missing[] = $ext;
                }
            }
            if (empty($missing)) {
                $this->line('  Extensions: All required ✅');
            } else {
                $this->error('  Missing: ' . implode(', ', $missing));
                return false;
            }
            return true;
        }
        $this->error('  PHP Version: ' . PHP_VERSION . ' (minimum: ' . $minVersion . ') ❌');
        return false;
    }
    /**
     * Check database connection and required tables
     */
    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->line('  Database: Connected ✅');
            // ADJUST: List of required tables
            $tables = [
                'users',
                'agencies',
                'documents',
                // add more tables as needed
            ];
            $missing = [];
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    $missing[] = $table;
                }
            }
            if (empty($missing)) {
                $this->line('  Tables: All exist ✅');
            } else {
                $this->error('  Missing: ' . implode(', ', $missing));
                $this->line('  Run: php artisan migrate');
                return false;
            }
            return true;
        } catch (\Exception $e) {
            $this->error('  Database: Connection failed ❌');
            $this->line('  Error: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Check storage directories
     */
    protected function checkStorage(): bool
    {
        // ADJUST: Directories to check
        $directories = [
            'app' => storage_path('app'),
            'attachments' => storage_path('app/attachments'),
            // add more directories as needed
        ];
        $allOk = true;
        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $this->line("  {$name}: Exists ✅");
            } else {
                if ($this->option('fix')) {
                    mkdir($path, 0755, true);
                    $this->line("  {$name}: Created ✅");
                } else {
                    $this->warn("  {$name}: Missing ({$path})");
                    $allOk = false;
                }
            }
        }
        return $allOk;
    }
    // ============================================================
    // OPTIONAL CHECKS - Uncomment and adjust as needed
    // ============================================================
    /**
     * Check Redis connection
     * Uncomment in handle() to enable
     */
    // protected function checkRedis(): bool
    // {
    //     try {
    //         \Illuminate\Support\Facades\Redis::connection()->ping();
    //         $this->line('  Redis: Connected ✅');
    //         return true;
    //     } catch (\Exception $e) {
    //         $this->warn('  Redis: Not available');
    //         return false;
    //     }
    // }
    /**
     * Check mail configuration
     * Uncomment in handle() to enable
     */
    // protected function checkMail(): bool
    // {
    //     $driver = config('mail.mailer');
    //     
    //     if ($driver === 'log') {
    //         $this->warn('  Mail: Using log driver (not recommended for production)');
    //         return false;
    //     }
    //     
    //     if (in_array($driver, ['smtp', 'ses', 'mailgun'])) {
    //         $this->line("  Mail: Using {$driver} ✅");
    //         return true;
    //     }
    //     
    //     $this->warn("  Mail: Driver '{$driver}' not configured");
    //     return false;
    // }
    /**
     * Check external API service (example: Ollama)
     * Uncomment in handle() to enable
     */
    // protected function checkOllama(): bool
    // {
    //     $host = 'http://localhost:11434'; // ADJUST: Your service host
    //     
    //     try {
    //         $response = shell_exec("curl -s --max-time 5 -o /dev/null -w '%{http_code}' {$host}/api/tags 2>/dev/null");
    //         
    //         if ($response == '200') {
    //             $this->line("  Ollama: Running at {$host} ✅");
    //             return true;
    //         }
    //         
    //         $this->warn("  Ollama: Not running at {$host}");
    //         return false;
    //     } catch (\Exception $e) {
    //         $this->warn('  Ollama: Not accessible');
    //         return false;
    //     }
    // }
    /**
     * Check required environment variables
     * Uncomment in handle() to enable
     */
    // protected function checkEnvVars(): bool
    // {
    //     // ADJUST: Required env vars
    //     $required = [
    //         'APP_KEY',
    //         'DB_CONNECTION',
    //         // add more vars
    //     ];
    //     
    //     $missing = [];
    //     foreach ($required as $var) {
    //         if (empty(env($var))) {
    //             $missing[] = $var;
    //         }
    //     }
    //     
    //     if (empty($missing)) {
    //         $this->line('  Env vars: All set ✅');
    //         return true;
    //     }
    //     
    //     $this->warn('  Missing: ' . implode(', ', $missing));
    //     return false;
    // }
}
