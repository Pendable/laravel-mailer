<?php

namespace Pendable\Mail\Tests;

use Illuminate\Foundation\Application;
use Pendable\Mail\PendableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mail.default', 'pendable');
        $app['config']->set('services.pendable.key', 'test-key');
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [PendableServiceProvider::class];
    }
}