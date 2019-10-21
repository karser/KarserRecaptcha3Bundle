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

![image](https://user-images.githubusercontent.com/1675033/58698825-bbca8e00-83a4-11e9-9627-e3a2b1a6c074.png)


Installation
------------

With [composer](https://getcomposer.org), require:

`composer require karser/karser-recaptcha3-bundle`


Register the bundle: 
--------------------

**Symfony 3 Version:**  
Register bundle into `app/AppKernel.php`:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Karser\Recaptcha3Bundle\KarserRecaptcha3Bundle(),
    );
}
```
**Symfony 4 Version :**   
Register bundle into `config/bundles.php`:
```php 
return [
    //...
    Karser\Recaptcha3Bundle\KarserRecaptcha3Bundle::class => ['all' => true],
];
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

### How to integrate re-captcha in Symfony form:

```php
<?php

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('captcha', Recaptcha3Type::class, [
            'constraints' => new Recaptcha3(),
            'action_name' => 'signup|resend_email|forgot_password',
        ]);
        //$builder->add(...);
    }
}
```

### How to integrate re-captcha in API method:

The idea is to require the frontend to submit the captcha token, so it will be validated on server side.

First you need to add the captcha field to your transport entity:
```php
<?php

namespace App\Dto\UseCases;

final class UserSignupRequest
{
    /** @var string|null */
    public $email;

    /** @var string|null */
    public $captcha;
}
```

And to add the validation constraint:

```yaml
#config/validator/validation.yaml
App\Dto\UserSignupRequest:
    properties:
        email:
            - NotBlank: ~
            - Email: { mode: strict }
        captcha:
            - Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3: ~
```


On frontend part you need to submit the captcha token along with email.
You can obtain the captcha token either *on page load* or *on form submit*.

```html
<script src="https://www.google.com/recaptcha/api.js?render=<siteKey>"></script>

<script>
const siteKey = '*****************-**-******-******';

//either on page load
grecaptcha.ready(function() {
    grecaptcha.execute(siteKey, {
        action: 'signup|resend_email|forgot_password'
    }).then(function(token) {
        //the token will be sent on form submit
        $('[name="captcha"]').val(token);
    });
});

//or on form post:
grecaptcha.ready(function() {
    grecaptcha.execute(siteKey, {
        action: 'signup|resend_email|forgot_password'
    }).then(function(token) {
        //submit the form
        return http.post(url, {email, captcha: token});
    });
});
</script>
```

### How to deal with functional and e2e testing:

Recaptcha won't allow you to test your app efficiently unless you disable it for the environment you are testing against.

```yaml
# app/config/config.yml (or config/packages/karser_recaptcha3.yaml if using Symfony4)
karser_recaptcha3:
    enabled: '%env(bool:RECAPTCHA3_ENABLED)%'
```

```bash
#.env.test or a stage server environment
RECAPTCHA3_ENABLED=0
```

### How to add Cloudflare IP resolver:

From the [Cloudflare docs](https://support.cloudflare.com/hc/en-us/articles/200170986-How-does-Cloudflare-handle-HTTP-Request-headers-):
To provide the client (visitor) IP address for every request to the origin, Cloudflare adds the CF-Connecting-IP header.
```
"CF-Connecting-IP: A.B.C.D"
```

So you can implement custom IP resolver which attempts to read the `CF-Connecting-IP` header or fallbacks with the internal IP resolver:
 
```php
<?php declare(strict_types=1);

namespace App\Service;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CloudflareIpResolver implements IpResolverInterface
{
    /** @var IpResolverInterface */
    private $decorated;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(IpResolverInterface $decorated, RequestStack $requestStack)
    {
        $this->decorated = $decorated;
        $this->requestStack = $requestStack;
    }

    public function resolveIp(): ?string
    {
        return $this->doResolveIp() ?? $this->decorated->resolveIp();
    }

    private function doResolveIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }
        return $request->server->get('HTTP_CF_CONNECTING_IP');
    }
}
```

Here is the service declaration. It decorates the internal resolver:
```yaml
#services.yaml
services:
    App\Service\CloudflareIpResolver:
        decorates: 'karser_recaptcha3.ip_resolver'
        arguments:
            $decorated: '@App\Service\CloudflareIpResolver.inner'
            $requestStack: '@request_stack'
```

Testing
-------

```
composer update
vendor/bin/phpunit
```
