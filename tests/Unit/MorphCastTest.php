<?php

declare(strict_types=1);

use HDaklue\LaravelDTOMorphCast\MorphCast;
use HDaklue\LaravelDTOMorphCast\Tests\Datasets\ModelInstance;
use Illuminate\Database\Eloquent\Relations\Relation;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

beforeEach(function () {
    Relation::morphMap([
        'model_instance' => ModelInstance::class,
    ]);
});

class BasicTestDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'documentable_type' => 'required|string',
            'documentable' => 'array',
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'documentable' => new MorphCast(),
        ];
    }
}

class SensitiveTestDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'documentable_type' => 'required|string',
            'documentable' => 'array',
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'documentable' => new MorphCast(['password', 'secret_token']),
        ];
    }
}

it('properly casts to the morphed model class', function () {
    $dto = new BasicTestDTO([
        'documentable_type' => 'model_instance',
        'documentable' => ['name' => 'Jane Doe', 'age' => 25],
    ]);

    $model = $dto->documentable;

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe(['name' => 'Jane Doe', 'age' => 25]);
});

it('hides sensitive data when specified in constructor', function () {
    $dto = new SensitiveTestDTO([
        'documentable_type' => 'model_instance',
        'documentable' => [
            'name' => 'Jane Doe',
            'age' => 25,
            'password' => 'secret123',
            'secret_token' => 'abc123',
            'public_data' => 'visible',
        ],
    ]);

    $model = $dto->documentable;

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
    $dto = new BasicTestDTO([
        'documentable_type' => 'model_instance',
        'documentable' => [
            'name' => 'Jane Doe',
            'password' => 'secret123',
        ],
    ]);

    $model = $dto->documentable;

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe([
            'name' => 'Jane Doe',
            'password' => 'secret123',
        ]);
});

it('handles empty values correctly', function () {
    $dto = new BasicTestDTO([
        'documentable_type' => 'model_instance',
        'documentable' => [],
    ]);

    $model = $dto->documentable;

    expect($model)->toBeInstanceOf(ModelInstance::class)
        ->and($model->toArray())->toBe([]);
});
