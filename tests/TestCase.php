<?php

namespace ThomasVantuycom\StatamicBrevo\Tests;

use Statamic\Testing\AddonTestCase;
use ThomasVantuycom\StatamicBrevo\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
