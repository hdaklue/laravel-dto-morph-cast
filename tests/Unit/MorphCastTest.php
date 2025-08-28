<?php

declare(strict_types=1);

use HDaklue\LaravelDTOMorphCast\MorphCast;
use HDaklue\LaravelDTOMorphCast\Tests\Datasets\ModelInstance;
use Illuminate\Database\Eloquent\Relations\Relation;
use WendellAdriel\ValidatedDTO\Exceptions\CastException;

beforeEach(function () {
    Relation::morphMap([
        'model_instance' => ModelInstance::class,
    ]);
});

it('properly casts to the morphed model class', function () {
    $dto = new class()
    {
        public array $dtoData = [
            'test_property_type' => 'model_instance',
        ];

        public function castProperty($value)
        {
            $cast = new MorphCast();

            return $cast->cast('test_property', $value);
        }
    };

    $model = $dto->castProperty(['name' => 'Jane Doe', 'age' => 25]);

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe(['name' => 'Jane Doe', 'age' => 25]);
});

it('throws exception if morph type key is missing', function () {
    $dto = new class()
    {
        public array $dtoData = [];

        public function castProperty($value)
        {
            $cast = new MorphCast();

            return $cast->cast('test_property', $value);
        }
    };

    $dto->castProperty(['name' => 'Jane Doe', 'age' => 25]);
})->throws(CastException::class, 'MorphCast: Missing morph type key [test_property_type] in DTO data.');

it('throws exception if model class is invalid', function () {
    $dto = new class()
    {
        public array $dtoData = [
            'test_property_type' => 'NonExistentModel',
        ];

        public function castProperty($value)
        {
            $cast = new MorphCast();

            return $cast->cast('test_property', $value);
        }
    };

    $dto->castProperty(['name' => 'Jane Doe', 'age' => 25]);
})->throws(CastException::class, 'MorphCast: Invalid model class [NonExistentModel].');

it('hides sensitive data when specified in constructor', function () {
    $dto = new class()
    {
        public array $dtoData = [
            'test_property_type' => 'model_instance',
        ];

        public function castProperty($value)
        {
            $cast = new MorphCast(['password', 'secret_token']);

            return $cast->cast('test_property', $value);
        }
    };

    $model = $dto->castProperty([
        'name' => 'Jane Doe',
        'age' => 25,
        'password' => 'secret123',
        'secret_token' => 'abc123',
        'public_data' => 'visible',
    ]);

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe([
            'name' => 'Jane Doe',
            'age' => 25,
            'public_data' => 'visible',
        ])
        ->and($model->toArray())->not()->toHaveKey('password')
        ->and($model->toArray())->not()->toHaveKey('secret_token');
});

it('works normally when no sensitive data is specified', function () {
    $dto = new class()
    {
        public array $dtoData = [
            'test_property_type' => 'model_instance',
        ];

        public function castProperty($value)
        {
            $cast = new MorphCast([]);

            return $cast->cast('test_property', $value);
        }
    };

    $model = $dto->castProperty([
        'name' => 'Jane Doe',
        'password' => 'secret123',
    ]);

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe([
            'name' => 'Jane Doe',
            'password' => 'secret123',
        ]);
});
