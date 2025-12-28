<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

/**
 * Architectural Tests for Expense Tracker
 *
 * These tests ensure that the codebase follows Laravel best practices
 * and architectural patterns using reflection and static analysis.
 */
#[Group('architecture')]
final class ArchitectureTest extends TestCase
{
    /**
     * Get all PHP files in a directory recursively
     */
    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get classes from directory
     */
    private function getClassesFromDirectory(string $directory, string $namespace): array
    {
        $classes = [];
        $files = $this->getPhpFiles($directory);

        foreach ($files as $file) {
            $relativePath = str_replace([$directory, '.php', '/'], ['', '', '\\'], $file);
            $className = $namespace . ltrim($relativePath, '\\');

            if (class_exists($className) || interface_exists($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

    #[Test]
    public function models_extend_eloquent_model(): void
    {
        $modelsDir = app_path('Models');
        $classes = $this->getClassesFromDirectory($modelsDir, 'App\\Models\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isSubclassOf('Illuminate\Database\Eloquent\Model'),
                "Model {$class} should extend Illuminate\\Database\\Eloquent\\Model"
            );
        }

        $this->assertNotEmpty($classes, 'At least one model should exist');
    }

    #[Test]
    public function controllers_have_controller_suffix(): void
    {
        $controllersDir = app_path('Http/Controllers');
        $classes = $this->getClassesFromDirectory($controllersDir, 'App\\Http\\Controllers\\');

        foreach ($classes as $class) {
            $this->assertStringEndsWith(
                'Controller',
                $class,
                "Controller {$class} should end with 'Controller' suffix"
            );
        }

        $this->assertNotEmpty($classes, 'At least one controller should exist');
    }

    #[Test]
    public function controllers_extend_base_controller(): void
    {
        $controllersDir = app_path('Http/Controllers');
        $classes = $this->getClassesFromDirectory($controllersDir, 'App\\Http\\Controllers\\');

        foreach ($classes as $class) {
            if ($class === 'App\\Http\\Controllers\\Controller') {
                continue; // Skip base controller
            }

            $reflection = new ReflectionClass($class);
            // Controllers should extend either base Controller or Laravel's Controller
            $extendsController = $reflection->isSubclassOf('App\Http\Controllers\Controller')
                || $reflection->isSubclassOf('Illuminate\Routing\Controller');

            $this->assertTrue(
                $extendsController,
                "Controller {$class} should extend a base Controller class"
            );
        }
    }

    #[Test]
    public function form_requests_have_request_suffix(): void
    {
        $requestsDir = app_path('Http/Requests');

        if (! is_dir($requestsDir)) {
            $this->markTestSkipped('No Form Requests directory found');
        }

        $classes = $this->getClassesFromDirectory($requestsDir, 'App\\Http\\Requests\\');

        foreach ($classes as $class) {
            $this->assertStringEndsWith(
                'Request',
                $class,
                "Form Request {$class} should end with 'Request' suffix"
            );
        }

        $this->assertNotEmpty($classes, 'At least one form request should exist');
    }

    #[Test]
    public function form_requests_extend_form_request(): void
    {
        $requestsDir = app_path('Http/Requests');

        if (! is_dir($requestsDir)) {
            $this->markTestSkipped('No Form Requests directory found');
        }

        $classes = $this->getClassesFromDirectory($requestsDir, 'App\\Http\\Requests\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isSubclassOf('Illuminate\Foundation\Http\FormRequest'),
                "Form Request {$class} should extend Illuminate\\Foundation\\Http\\FormRequest"
            );
        }
    }

    #[Test]
    public function form_requests_have_rules_method(): void
    {
        $requestsDir = app_path('Http/Requests');

        if (! is_dir($requestsDir)) {
            $this->markTestSkipped('No Form Requests directory found');
        }

        $classes = $this->getClassesFromDirectory($requestsDir, 'App\\Http\\Requests\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->hasMethod('rules'),
                "Form Request {$class} should have a 'rules' method"
            );
        }
    }

    #[Test]
    public function middleware_is_in_correct_namespace(): void
    {
        $middlewareDir = app_path('Http/Middleware');
        $classes = $this->getClassesFromDirectory($middlewareDir, 'App\\Http\\Middleware\\');

        foreach ($classes as $class) {
            $this->assertStringStartsWith(
                'App\\Http\\Middleware\\',
                $class,
                "Middleware {$class} should be in App\\Http\\Middleware namespace"
            );
        }

        $this->assertNotEmpty($classes, 'At least one middleware should exist');
    }

    #[Test]
    public function middleware_has_handle_method(): void
    {
        $middlewareDir = app_path('Http/Middleware');
        $classes = $this->getClassesFromDirectory($middlewareDir, 'App\\Http\\Middleware\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->hasMethod('handle'),
                "Middleware {$class} should have a 'handle' method"
            );
        }
    }

    #[Test]
    public function providers_have_provider_suffix(): void
    {
        $providersDir = app_path('Providers');
        $classes = $this->getClassesFromDirectory($providersDir, 'App\\Providers\\');

        foreach ($classes as $class) {
            $this->assertStringEndsWith(
                'Provider',
                $class,
                "Provider {$class} should end with 'Provider' suffix"
            );
        }

        $this->assertNotEmpty($classes, 'At least one provider should exist');
    }

    #[Test]
    public function providers_extend_service_provider(): void
    {
        $providersDir = app_path('Providers');
        $classes = $this->getClassesFromDirectory($providersDir, 'App\\Providers\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isSubclassOf('Illuminate\Support\ServiceProvider'),
                "Provider {$class} should extend Illuminate\\Support\\ServiceProvider"
            );
        }
    }

    #[Test]
    public function factories_have_factory_suffix(): void
    {
        $factoriesDir = database_path('factories');
        $classes = $this->getClassesFromDirectory($factoriesDir, 'Database\\Factories\\');

        foreach ($classes as $class) {
            $this->assertStringEndsWith(
                'Factory',
                $class,
                "Factory {$class} should end with 'Factory' suffix"
            );
        }

        $this->assertNotEmpty($classes, 'At least one factory should exist');
    }

    #[Test]
    public function factories_extend_base_factory(): void
    {
        $factoriesDir = database_path('factories');
        $classes = $this->getClassesFromDirectory($factoriesDir, 'Database\\Factories\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isSubclassOf('Illuminate\Database\Eloquent\Factories\Factory'),
                "Factory {$class} should extend Illuminate\\Database\\Eloquent\\Factories\\Factory"
            );
        }
    }

    #[Test]
    public function no_eval_or_exec_statements(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        $dangerousFunctions = ['eval(', 'exec(', 'system(', 'shell_exec('];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            foreach ($dangerousFunctions as $function) {
                $this->assertStringNotContainsString(
                    $function,
                    $content,
                    "File {$file} should not contain dangerous function: {$function}"
                );
            }
        }
    }

    #[Test]
    public function no_debug_statements_in_application_code(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        $debugFunctions = ['dd(', 'dump(', 'var_dump(', 'print_r('];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check each function, but allow 'array(' which contains 'ray('
            foreach ($debugFunctions as $debugFunction) {
                // Skip if it's part of a longer word like 'array('
                if ($debugFunction === 'ray(' && str_contains($content, 'array(')) {
                    // Only fail if we find standalone ray(
                    $pattern = '/(?<!ar)ray\(/';
                    if (preg_match($pattern, $content)) {
                        $this->fail("File {$file} should not contain debug statement: ray()");
                    }
                } else {
                    $this->assertStringNotContainsString(
                        $debugFunction,
                        $content,
                        "File {$file} should not contain debug statement: {$debugFunction}"
                    );
                }
            }
        }
    }

    #[Test]
    public function models_have_proper_fillable_or_guarded(): void
    {
        $modelsDir = app_path('Models');
        $classes = $this->getClassesFromDirectory($modelsDir, 'App\\Models\\');

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);

            // Models should have either $fillable or $guarded property
            $hasFillable = $reflection->hasProperty('fillable');
            $hasGuarded = $reflection->hasProperty('guarded');

            $this->assertTrue(
                $hasFillable || $hasGuarded,
                "Model {$class} should have either \$fillable or \$guarded property for mass assignment protection"
            );
        }

        $this->assertNotEmpty($classes, 'At least one model should exist');
    }

    #[Test]
    public function application_code_does_not_use_env_helper_directly(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        // Allowed files that can use env()
        $allowedFiles = [
            app_path('Providers'),
            app_path('Http/Middleware/Authenticate.php'),
        ];

        foreach ($files as $file) {
            // Skip allowed files
            $isAllowed = false;
            foreach ($allowedFiles as $allowedPath) {
                if (str_contains($file, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }

            if ($isAllowed) {
                continue;
            }

            $content = file_get_contents($file);
            $this->assertStringNotContainsString(
                'env(',
                $content,
                "File {$file} should not use env() helper directly. Use config() instead."
            );
        }
    }

    // ===== New Advanced Architectural Rules =====

    #[Test]
    public function models_do_not_reference_controllers(): void
    {
        $modelsDir = app_path('Models');
        $files = $this->getPhpFiles($modelsDir);

        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for controller references
            $this->assertStringNotContainsString(
                'App\\Http\\Controllers',
                $content,
                "Model file {$file} should not reference Controllers. Models should be dumb data structures."
            );

            $this->assertStringNotContainsString(
                'use App\Http\Controllers',
                $content,
                "Model file {$file} should not import Controllers"
            );
        }
    }

    #[Test]
    public function controllers_do_not_call_other_controllers(): void
    {
        $controllersDir = app_path('Http/Controllers');
        $classes = $this->getClassesFromDirectory($controllersDir, 'App\\Http\\Controllers\\');

        $testedControllers = 0;

        foreach ($classes as $class) {
            if ($class === 'App\\Http\\Controllers\\Controller') {
                continue; // Skip base controller
            }

            $testedControllers++;
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            // Check constructor dependencies
            if ($constructor) {
                $parameters = $constructor->getParameters();
                foreach ($parameters as $param) {
                    $type = $param->getType();
                    if ($type && ! $type->isBuiltin()) {
                        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : '';
                        $this->assertStringNotContainsString(
                            'Controller',
                            $typeName,
                            "Controller {$class} should not depend on other Controllers. Use Services instead."
                        );
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $testedControllers, 'At least one controller should be tested');
    }

    #[Test]
    public function views_do_not_contain_database_queries(): void
    {
        $viewsDir = resource_path('views');
        if (! is_dir($viewsDir)) {
            $this->markTestSkipped('No views directory found');
        }

        $files = $this->getPhpFiles($viewsDir);
        $bladeFiles = glob($viewsDir . '/**/*.blade.php');
        $allFiles = array_merge($files, $bladeFiles);

        $dbPatterns = [
            'DB::',
            '::where(',
            '::find(',
            '::all(',
            '::get(',
            '::first(',
            '::query(',
        ];

        foreach ($allFiles as $file) {
            $content = file_get_contents($file);

            foreach ($dbPatterns as $pattern) {
                $this->assertStringNotContainsString(
                    $pattern,
                    $content,
                    "View file {$file} should not contain database queries. This causes N+1 problems. Pass data from controller."
                );
            }
        }
    }

    #[Test]
    public function all_php_files_use_strict_types(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        foreach ($files as $file) {
            $content = file_get_contents($file);

            $this->assertStringContainsString(
                'declare(strict_types=1);',
                $content,
                "File {$file} must declare strict types for type safety"
            );
        }

        $this->assertNotEmpty($files, 'At least one PHP file should exist in app directory');
    }

    #[Test]
    public function controller_methods_have_return_types(): void
    {
        $controllersDir = app_path('Http/Controllers');
        $classes = $this->getClassesFromDirectory($controllersDir, 'App\\Http\\Controllers\\');

        foreach ($classes as $class) {
            if ($class === 'App\\Http\\Controllers\\Controller') {
                continue; // Skip base controller
            }

            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                // Skip inherited methods and magic methods
                if ($method->getDeclaringClass()->getName() !== $class || str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $this->assertTrue(
                    $method->hasReturnType(),
                    "Controller method {$class}::{$method->getName()}() must declare a return type"
                );
            }
        }
    }

    #[Test]
    public function no_raw_sql_queries(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        $rawSqlPatterns = [
            'DB::statement(',
            'DB::raw(',
            'DB::select(',
            'DB::insert(',
            'DB::update(',
            'DB::delete(',
            '->raw(',
        ];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            foreach ($rawSqlPatterns as $pattern) {
                $this->assertStringNotContainsString(
                    $pattern,
                    $content,
                    "File {$file} should not use raw SQL ({$pattern}). Use Eloquent or Query Builder to prevent SQL injection."
                );
            }
        }
    }

    #[Test]
    public function all_models_have_factories(): void
    {
        $modelsDir = app_path('Models');
        $models = $this->getClassesFromDirectory($modelsDir, 'App\\Models\\');

        foreach ($models as $model) {
            $modelName = class_basename($model);
            $factoryClass = "Database\\Factories\\{$modelName}Factory";

            $this->assertTrue(
                class_exists($factoryClass),
                "Model {$model} must have a corresponding factory {$factoryClass}. Use factories instead of manual seeding."
            );
        }

        $this->assertNotEmpty($models, 'At least one model should exist');
    }

    #[Test]
    public function models_do_not_use_request_helper(): void
    {
        $modelsDir = app_path('Models');
        $files = $this->getPhpFiles($modelsDir);

        foreach ($files as $file) {
            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'request(',
                $content,
                "Model file {$file} should not use request() helper. Models should not know about HTTP requests."
            );

            $this->assertStringNotContainsString(
                'Request::',
                $content,
                "Model file {$file} should not use Request facade"
            );
        }
    }

    #[Test]
    public function no_die_or_exit_statements(): void
    {
        $appDir = app_path();
        $files = $this->getPhpFiles($appDir);

        foreach ($files as $file) {
            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'die(',
                $content,
                "File {$file} should not use die(). It kills the PHP process and prevents Laravel lifecycle completion."
            );

            $this->assertStringNotContainsString(
                'exit(',
                $content,
                "File {$file} should not use exit(). It kills the PHP process and prevents Laravel lifecycle completion."
            );
        }
    }

    #[Test]
    public function controllers_follow_resourceful_naming(): void
    {
        $controllersDir = app_path('Http/Controllers');
        $classes = $this->getClassesFromDirectory($controllersDir, 'App\\Http\\Controllers\\');

        $resourcefulMethods = [
            'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        ];

        foreach ($classes as $class) {
            if ($class === 'App\\Http\\Controllers\\Controller') {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                // Skip inherited methods, magic methods, and constructor
                if ($method->getDeclaringClass()->getName() !== $class
                    || str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $methodName = $method->getName();

                // Common non-resourceful anti-patterns
                $antiPatterns = [
                    'get_all', 'getAll', 'getData', 'get_data',
                    'do_', 'process_', 'handle_',
                ];

                foreach ($antiPatterns as $antiPattern) {
                    $this->assertStringNotContainsString(
                        $antiPattern,
                        $methodName,
                        "Controller method {$class}::{$methodName}() uses non-standard naming. Use resourceful methods (index, create, store, show, edit, update, destroy) or descriptive names like 'daily', 'monthly'."
                    );
                }
            }
        }
    }
}
