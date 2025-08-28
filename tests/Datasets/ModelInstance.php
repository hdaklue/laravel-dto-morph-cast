<?php

declare(strict_types=1);

namespace HDaklue\LaravelDTOMorphCast\Tests\Datasets;

use Illuminate\Database\Eloquent\Model;

class ModelInstance extends Model
{
    protected $fillable = ['name', 'age'];
}