# Laravel SQL-Patch

Managing DML (Data Manipulation Language) statements during code deployment can be challenging, especially if included in database migrations designed for DDL (Data Definition Language) operations. This package provides a solution to handle DML operations separately, ensuring smooth and error-free deployments.

## Why Use Laravel SQL-Patch?

Incorporating DML statements into migrations can lead to:
- **Re-run Errors:** DML statements might fail if the data already exists or conditions are not met when re-running migrations in a new setup.
- **Separation of Concerns:** Keeping schema changes (DDL) and data changes (DML) separate enhances clarity and maintainability of your codebase.

## Features

- **Seamless DML Management:** Easily create and manage DML patches without interfering with migration files.
- **Idempotent Patches:** Ensure DML operations can be safely executed multiple times.
- **Structured Deployment:** Maintain a clear separation between schema changes and data changes.

## Installation

Install the package using Composer:

```bash
composer require dweik/laravel-database-patching
```

## Creating a New Patch File
To create a new patch file, use the following Artisan command. The patch file will be located `in database/patches/...`:
```bash
php artisan sql-patch:make SomeClassName
```

Add your DML code under the \`**handler**` method in the generated patch file.


## Running Patch Files
To run the new patch files, use the following command:
```bash
php artisan sql-patch
```


## Example
Here's an example of how to create and use a patch file:

1. **Create a Patch File:**
```bash
php artisan sql-patch:make UpdateUserDefaults
```

2. **Edit the Patch File:**
```php
// database/patches/2024_08_04_104717_UpdateUserDefaults.php
use LaravelDatabasePatching\Interfaces\SQLPatchInterface;
use \Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserDefaults implements SQLPatchInterface
{
    public function handler()
    {
        // Ensure the column exists before updating
        if (Schema::hasColumn('users', 'default_column')) {
            DB::table('users')->whereNull('default_column')->update(['default_column' => 'default_value']);
        }
    }
}

```
3. **Run the Patch Files:**

```bash
php artisan sql-patch
```

## Best Practices
* **Idempotency:** Ensure all DML operations are idempotent to avoid issues when scripts are executed multiple times.
* **Testing:** Test your patches in a staging environment before deploying to production.
* **Version Control:** Keep your patch files under version control to track changes and collaborate effectively.


## Conclusion
The Laravel SQL-Patch package simplifies the management of DML operations during production deployments, ensuring they are handled separately from migrations. This approach minimizes errors and maintains a clean and organized codebase.

___

Feel free to adjust further as needed for your specific package details and requirements.
