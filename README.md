[![Latest Stable Version](https://poser.pugx.org/riconect/mailerbundle/v/stable)](https://packagist.org/packages/riconect/mailerbundle) [![License](https://poser.pugx.org/riconect/mailerbundle/license)](https://packagist.org/packages/riconect/mailerbundle)

#### This bundle is a mail helper for Symfony framework.

At present, it only works with the Doctrine ODM (MongoDB).

## Installation and configuration


### 1. Install via Composer

``` sh
$ composer require "riconect/mailerbundle:^1.0"
```

### 2. Add the bundle to your application's kernel

``` php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new Riconect\MailerBundle\RiconectMailerBundle(),
        // ...
    ];
}
```

### 3. Configure SwiftMailer to use the bundle

``` yaml
# app/config/config.yml
swiftmailer:
    spool:
        type:  service
        id:    riconect_mailer.spool
```

That's it. Now your emails is saved to default database in `messages` collection.

### 4. Add cron job.

``` sh
$ crontab -e
```
Add this line to execute command every minute:

```
* * * * * /usr/bin/php /PATH/TO/YOUR/PROJECT/bin/console swiftmailer:spool:send --message-limit=100 --env=dev > /dev/null
```
Don't forget to change options like `--env=prod` in production server.

If you wish to keep sent emails in database, configure the bundle:

``` yaml
# app/config/config.yml
riconect_mailer:
    keep_sent_emails: true
```


Default Configuration:

``` yaml
riconect_mailer:
    database_type:        mongodb
    keep_sent_emails:     false
    message_class:        Riconect\MailerBundle\Document\Message

```