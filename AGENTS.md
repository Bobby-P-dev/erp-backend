<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== modular-monolith-clean-code rules ===

# Clean Code & Modular Monolith Guidelines

## 1. Domain & Module Separation (Modular Monolith)
- Proyek ini menerapkan arsitektur **Modular Monolith**.
- Setiap modul bisnis dipisahkan ke dalam folder domain masing-masing pada setiap layer:
  - `Core`: Master data utama perusahaan (Company, Division, Position, JobLevel, Employee, User, Role, Permission, Accounting).
  - `Purchasing`: Pengadaan barang, Purchase Requisitions, Supplier & relasi sub-entitas (Supplier Contacts, Bank Accounts, Documents, Supplier Items catalog).
  - Modul baru di masa mendatang (e.g. `Inventory`, `Sales`, `Finance`) wajib mengikuti pola modular ini.
- Struktur folder per layer harus mencerminkan nama modul:
  - `app/Models/{Module}/`
  - `app/Repositories/{Module}/`
  - `app/Services/{Module}/`
  - `app/Http/Controllers/Api/{Version}/{Module}/`
  - `app/Http/Requests/{Module}/`
  - `app/Http/Resources/{Module}/`
  - `database/factories/{Module}/`
  - `tests/Feature/{Module}/`

## 2. Repository & Service Flow Pattern
- **Repository (Wajib untuk Query Database)**:
  - Semua query database / Eloquent (where, filtering, joins, search, pagination) WAJIB diisolasi di dalam Repository.
  - Controller dan Service dilarang memanggil query Eloquent langsung jika ada layer data yang dapat diabstraksi.
- **Service (Untuk Business Logic)**:
  - Gunakan Service jika terdapat logika bisnis yang berat, orkestrasi multi-step, DB Transaction lintas tabel, auto-generate kode dokumen, kalkulasi harga/pajak, atau auto-reset status (misalnya status `is_primary`).
  - Jangan membuat Service kosong yang hanya menjadi passthrough proxy ke Repository jika tidak ada logika bisnis di dalamnya.
- **Alur Arsitektur yang Sah**:
  1. `Controller -> Repository`: Untuk CRUD sederhana dan query langsung tanpa logika bisnis kompleks.
  2. `Controller -> Service -> Repository`: Untuk operasi bisnis kompleks, multi-step, transaksi, dan orkestrasi.
  3. `Controller -> Service`: Diizinkan tanpa Repository jika Service murni mengurus third-party API, perhitungan matematis/utilitas, atau notifikasi.

## 3. Dependency Injection & PHP 8.3 Standards
- Wajib gunakan **PHP 8 Constructor Property Promotion** dengan dependency injection container Laravel:
  ```php
  public function __construct(
      protected SupplierRepository $repository,
      protected SupplierService $service
  ) {}
  ```
- **DILARANG KERAS** melakukan instansiasi manual dengan operator `new` di dalam controller (contoh: `$this->repo = new SupplierRepository;`).

## 4. Form Request Rules
- **Wajib Gunakan Form Request** jika jumlah field input request **> 4 parameter**, atau jika validasi melibatkan aturan kompleks (misal regex, conditional rules, exists/unique rule dengan pengecualian ID).
- Inline validation (`$request->validate([...])`) hanya diizinkan untuk request kecil yang `<= 4 parameter` sederhana.
- Konvensi penamaan Form Request: `{Action}{Model}Request` (contoh: `StoreSupplierRequest`, `UpdateSupplierRequest`, `StoreEmployeeRequest`).

## 5. API Response & Eloquent Resources
- **Setiap endpoint API WAJIB menggunakan Eloquent API Resource**. Dilarang mengembalikan raw Model Eloquent atau raw array data.
- Resource harus berada di dalam namespace modul masing-masing (`App\Http\Resources\{Module}\{Model}Resource`).
- Response JSON harus konsisten menyediakan key `message` dan `data` (serta `meta` untuk pagination).

</laravel-boost-guidelines>
