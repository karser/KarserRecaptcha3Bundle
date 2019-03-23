KarserRecaptcha3Bundle
======================

[![Build Status](https://travis-ci.org/karser/KarserRecaptcha3Bundle.svg?branch=master)](https://travis-ci.org/karser/KarserRecaptcha3Bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/karser/KarserRecaptcha3Bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/karser/KarserRecaptcha3Bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/karser/KarserRecaptcha3Bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/karser/KarserRecaptcha3Bundle/?branch=master)
[![Total Downloads](https://poser.pugx.org/karser/karser-recaptcha3-bundle/downloads)](https://packagist.org/packages/karser/karser-recaptcha3-bundle)

reCAPTCHA v3 returns a score for each request without user friction. 
The score is based on interactions with your site (1.0 is very likely a good interaction,
0.0 is very likely a bot) and enables you to
take an appropriate action for your site. Register reCAPTCHA v3 keys
[here](https://g.co/recaptcha/v3).


Installation
------------

With [composer](https://getcomposer.org), require:

`composer require karser/karser-recaptcha3-bundle`

Then enable it in your kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        //...
        new Karser\Recaptcha3Bundle\KarserRecaptcha3Bundle(),
        //...
```

Configuration
-------------

```yaml
# app/config/config.yml (or config/packages/karser_recaptcha3.yaml if using Symfony4)
karser_recaptcha3:
    site_key: 'key'
    secret_key: 'secret'
    score_threshold: 0.5
    enabled: true
```

Usage
-----
```
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('captcha', Recaptcha3Type::class, [
            'constraints' => new Recaptcha3(),
        ]);
        //$builder->add(...);
    }
}
```


Testing
-------

```
composer update
vendor/bin/phpunit
```
