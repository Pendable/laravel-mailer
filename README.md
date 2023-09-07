Pendable Laravel Mailer
=================

Provides Pendable integration for Laravel. This package utilizes the [pendable-symfony](https://github.com/Pendable/symfony-mailer) mailer under the hood.

Pendable 
------------

Pendable provides a wrapper service around Amazon SES adding capabilities to Parallelize your Email Sending, Prioritize your Emails, Schedule delivery in the future, Track Open, Click and other events, Active throttling based on your Bounces and Complaints, Filter Spam Domains, Maintain a healthy Contact list, Retrieve, Troubleshoot, and Resend messages, Timeline view of all customer communication.

More info on [pendable.io](https://pendable.io/documentation)

Installation
------------

Open a command console in your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require pendable/laravel-mailer
```

Then add your pendable api key to your .env file

```bash

# Pendable API Key from https://pendable.io
PENDABLE_API_KEY=your-api-key

# Use the pendable mailer 
MAIL_MAILER=pendable
```

## Usage

The Pendable Mailer provides a drop-in replacement for the Laravel Mailer.
To send a message just simply use the `Mail` facade as you would normally do.

```php
Mail::to('my-email@example.com')->send(new MyMailable);
```

## Advanced Usage

### Setting options on runtime

```php

// Laravel mailable instance
$mailable = new MyMailable;

// adding tags
$mailable->tag('my-tag-1');
$mailable->tag('my-tag-2');

// adding custom fields
$mailable->metadata('custom_1', 'one');
$mailable->metadata('custom_2', 'two');

$mailable->withSymfonyMessage(function(Email $message){

    // set the priority
    $message->getHeaders()->addTextHeader('priority', 60);

    // set the config identifier
    $message->getHeaders()->addTextHeader('config_identifier', 'my-config');

    // set the client email id (usually your system's unique identifier)
    $message->getHeaders()->addTextHeader('client_email_id', '1');

    // set the schedule send at (in ISO 8601 format)
    $message->getHeaders()->addTextHeader('schedule_send_at', '2023-06-25T22:37:26+05:30');
});


// send the mail
Mail::to('my-email@example.com')->send($mailable);

```

### Setting options on the mailable class itself

```php

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class MyMailable extends Mailable {

    use Queueable, SerializesModels;

    public function headers(): Headers
    {
        return new Headers(
            text: [
                // set the priority
                'priority' => '60',
                
                // set the config identifier
                'config_identifier' => 'my-config',
                
                // set the client email id (usually your system's unique identifier)
                'client_email_id' => '1',
                
                // set the schedule send at (in ISO 8601 format)
                'schedule_send_at' => '2023-06-25T22:37:26+05:30',
            ],
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'My Mailer from Laravel + Pendable',
            
            // Setting tags
            tags: ['test', 'mailer', 'laravel'],
            
            // Setting custom fields
            metadata: [
                'custom_1' => 'one',
                'custom_2' => 'two',
            ],
        );
    }
    
    // ... 
}

# To send the mail
Mail::to('my-email@example.com')->send(new MyMailable);

```

## Resources

* [Report issues](https://github.com/pendable/laravel-mailer)
* [Pendable Documentation](https://pendable.io/documentation)

