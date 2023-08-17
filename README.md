Pendable Laravel Mailer
=================

Provides Pendable integration for Laravel.

Installation
------------

Open a command console in your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require pendable/laravel-mailer
```


Then add you pendable api key to your .env file

```bash
PENDABLE_API_KEY=your-api-key

# Use the pendable mailer 
MAIL_MAILER=pendable
```

## Usage

```php
Mail::to('myemail@test.com')->send(new TestMailer());
```

Resources
---------

* [Report issues](https://github.com/pendable/pendable-mailer)
* [Pendable Documentation](https://pendable.io/documentation)

