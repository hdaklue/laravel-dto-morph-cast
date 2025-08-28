<?php

declare(strict_types=1);

namespace HDaklue\LaravelDTOMorphCast\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            \WendellAdriel\ValidatedDTO\Providers\ValidatedDTOServiceProvider::class,
        ];
    }
}