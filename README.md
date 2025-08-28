# Laravel DTO MorphCast

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hdaklue/laravel-dto-morph-cast.svg?style=flat-square)](https://packagist.org/packages/hdaklue/laravel-dto-morph-cast)
[![Tests](https://github.com/hdaklue/laravel-dto-morph-cast/workflows/run-tests/badge.svg)](https://github.com/hdaklue/laravel-dto-morph-cast/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/hdaklue/laravel-dto-morph-cast.svg?style=flat-square)](https://packagist.org/packages/hdaklue/laravel-dto-morph-cast)

A powerful MorphCast plugin for Laravel Validated DTO that provides dynamic polymorphic casting for Eloquent models. This package allows you to dynamically cast data to different Eloquent model instances based on morphable type information within your DTOs.

## Features

- **Dynamic Model Resolution**: Automatically resolves model classes using Laravel's morph map
- **Polymorphic Casting**: Cast data to different model types based on morph type keys
- **Sensitive Data Protection**: Hide sensitive fields from being cast to model instances
- **Seamless Integration**: Works perfectly with Laravel Validated DTO package
- **Type Safety**: Validates model classes and throws meaningful exceptions
- **Convention Based**: Follows Laravel's morphable naming conventions

## Installation

You can install the package via Composer:

```bash
composer require hdaklue/laravel-dto-morph-cast
```

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- Laravel Validated DTO 4.0 or higher

## Usage

### Basic Usage

The MorphCast class automatically resolves polymorphic relationships in your DTOs. Here's how to use it:

```php
use HDaklue\LaravelDTOMorphCast\MorphCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;
use WendellAdriel\ValidatedDTO\Casting\Castable;

class MyDTO extends ValidatedDTO implements Castable
{
    public function casts(): array
    {
        return [
            'commentable' => new MorphCast(), // Will look for 'commentable_type' key
            'user' => new MorphCast(['password', 'api_token']), // Hide sensitive fields
        ];
    }
}
```

### Setting Up Morph Maps

First, define your morph map in a service provider:

```php
use Illuminate\Database\Eloquent\Relations\Relation;

// In a service provider boot method
Relation::morphMap([
    'post' => App\Models\Post::class,
    'video' => App\Models\Video::class,
    'user' => App\Models\User::class,
]);
```

### DTO Data Structure

Your DTO data should include the morph type key:

```php
$data = [
    'commentable_type' => 'post', // This maps to App\Models\Post via morph map
    'commentable' => [
        'id' => 1,
        'title' => 'My Post Title',
        'content' => 'Post content...',
        'created_at' => '2024-01-01 00:00:00'
    ]
];

$dto = new MyDTO($data);

// The 'commentable' property will be cast to a Post model instance
$post = $dto->commentable; // instanceof App\Models\Post
echo $post->title; // "My Post Title"
```

### Hiding Sensitive Data

You can hide sensitive fields from being cast to the model by passing them as an array to the constructor:

```php
use HDaklue\LaravelDTOMorphCast\MorphCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;
use WendellAdriel\ValidatedDTO\Casting\Castable;

class MyDTO extends ValidatedDTO implements Castable
{
    public function casts(): array
    {
        return [
            'user' => new MorphCast(), // Normal casting
            'sensitive_user' => new MorphCast(['password', 'secret_key']), // Hide sensitive fields
        ];
    }
}
```

You can also use Laravel's array-based casting syntax:

```php
public function casts(): array
{
    return [
        'user' => MorphCast::class,
        'sensitive_user' => [MorphCast::class, ['password', 'api_token', 'secret']],
    ];
}
```

When sensitive fields are specified, they will be excluded from the `forceFill()` operation:

```php
$data = [
    'sensitive_user_type' => 'user',
    'sensitive_user' => [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',        // This will be hidden
        'api_token' => 'token123',        // This will be hidden
        'created_at' => '2024-01-01 00:00:00'
    ]
];

$dto = new MyDTO($data);
$user = $dto->sensitive_user;

// Only non-sensitive data is available
echo $user->name; // "John Doe"
echo $user->email; // "john@example.com"
// $user->password and $user->api_token are not set
```

### Naming Conventions

By default, MorphCast follows Laravel's polymorphic relationship conventions:

- For a property named `commentable`, it looks for `commentable_type`
- For a property named `imageable`, it looks for `imageable_type`

### Custom Key Resolution

You can extend MorphCast to customize key resolution:

```php
class CustomMorphCast extends MorphCast
{
    protected function resolveMorphKeys(string $property): array
    {
        return [
            "custom_{$property}_type", // Custom type key format
            "custom_{$property}_id",   // Custom ID key format  
        ];
    }
}
```

### Error Handling

MorphCast provides clear error messages for common issues:

```php
// Missing morph type key
// Throws: "MorphCast: Missing morph type key [commentable_type] in DTO data."

// Invalid model class
// Throws: "MorphCast: Invalid model class [InvalidClass]."
```

## How It Works

1. **Trace Analysis**: Uses debug backtrace to access the parent DTO's data array
2. **Key Resolution**: Automatically determines morph type keys based on property names
3. **Model Resolution**: Uses Laravel's morph map to resolve the actual model class
4. **Validation**: Ensures the resolved class exists and extends Eloquent Model
5. **Instantiation**: Creates and fills the model instance with the provided data

## Performance Considerations

### Minimal Debug Backtrace Usage

This package uses `debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)` with a strict limit of 2 levels to access the parent DTO's data array. This approach provides several benefits:

- **Minimal Overhead**: Only traces 2 stack frames (current + parent), not the entire call stack
- **Targeted Access**: Specifically retrieves only the object data needed for casting
- **Auto-Resolution**: Eliminates the need for manual configuration or explicit passing of morph type information
- **Clean API**: Maintains a simple, intuitive interface without complex setup requirements

The small performance cost of this limited backtrace is offset by the significant flexibility gain of automatic morph type resolution, making polymorphic casting seamless and convention-based.

## Testing

```bash
composer test
```

## Code Style

This package uses Laravel Pint for code formatting:

```bash
composer lint
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to hassan@daklue.com.

## Credits

- [Hassan Ibrahim](https://github.com/hdaklue)
- Built as an extension for [Laravel Validated DTO](https://github.com/WendellAdriel/laravel-validated-dto)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Related

- [Laravel Validated DTO](https://github.com/WendellAdriel/laravel-validated-dto) - The main package this extends