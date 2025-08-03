<?php

use ClarkeWing\LegacySync\Tests\TestCase;
use Illuminate\Support\Str;

uses(TestCase::class)->in(__DIR__);

function laravelVersion(): int
{
    return (int) Str::before(app()->version(), '.');
}
