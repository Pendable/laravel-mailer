<?php

namespace Pendable\Mail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;
use Pendable\SymfonyMailer\Transport\PendableTransportFactory;

class PendableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/services.php', 'services');
        Mail::extend('pendable', function (array $config = []) {
            return (new PendableTransportFactory)->create(new Dsn(
                'pendable+api',
                'default',
                config('services.pendable.key'),
            ));
        });
    }
}
