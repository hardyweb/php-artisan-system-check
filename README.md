# php-artisan-system-check
check the system 



Create a Laravel artisan command `system:check` that verifies system setup.
Requirements:
1. Check PHP version (minimum 8.2) and extensions: pdo, mbstring, openssl, curl, zip, gd
2. Check database connection and tables exist: users, [CUSTOM_TABLES]
3. Check storage directories exist: app, [CUSTOM_DIRECTORIES]
4. Optional: Check [SERVICE_NAME] at [HOST:PORT]
5. Use --fix option to auto-create missing directories
6. Return exit code 0 if all pass, 1 if failed
7. Show summary: passed, warnings, failed counts
Format output with ✅ for pass, ❌ for fail, ⚠️ for warnings.
Use sections with `▸ Title` and `─` dividers.
Example structure:
- handle() method runs all checks
- Each checkXxx() returns bool
- Use `$this->line()`, `$this->error()`, `$this->warn()`
Customize for [PROJECT_NAME]:
- Tables: [LIST_TABLES]
- Directories: [LIST_DIRS]
- Additional services: [SERVICE_CHECKS]
