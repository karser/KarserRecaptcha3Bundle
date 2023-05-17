KarserRecaptcha3Bundle
======================

[![Build Status](https://github.com/karser/KarserRecaptcha3Bundle/workflows/Code_Checks/badge.svg)](https://github.com/karser/KarserRecaptcha3Bundle/actions)
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

You can quickly configure this bundle by using symfony/flex:
- answer **no** for `google/recaptcha` 
- answer **yes** for `karser/karser-recaptcha3-bundle`
![image](https://user-images.githubusercontent.com/1675033/73133604-d5a39a00-4033-11ea-9ef1-0fed12a8763b.png)

Configuration without symfony/flex: 
--------------------

### 1. Register the bundle

**Symfony 4/5/6 Version :**   
Register bundle into `config/bundles.php`:
```php 
return [
    //...
    Karser\Recaptcha3Bundle\KarserRecaptcha3Bundle::class => ['all' => true],
];
```

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

### 2. Add configuration files

```yaml
# config/packages/karser_recaptcha3.yaml (or app/config/config.yml if using Symfony3)

karser_recaptcha3:
    site_key: '%env(RECAPTCHA3_KEY)%'
    secret_key: '%env(RECAPTCHA3_SECRET)%'
    score_threshold: 0.5
```

Add your site key and secret to your .env file:
```
###> karser/recaptcha3-bundle ###
RECAPTCHA3_KEY=my_site_key
RECAPTCHA3_SECRET=my_secret
###< karser/recaptcha3-bundle ###
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
            'action_name' => 'homepage',
            'script_nonce_csp' => $nonceCSP,
            'locale' => 'de',
        ]);
    }
}
```
Notes:
- The `action_name` parameter is [reCAPTCHA v3 action](https://developers.google.com/recaptcha/docs/v3#actions) which identifies the submission of this particular form in the Google reCAPTCHA dashboard, and confirming it is as expected in the backend is a recommended extra security step.
- The `script_nonce_csp` parameter is optional. You must use the same nonce as in your Content-Security Policy header.
- The `locale` parameter is optional. It defaults to English and controls the language on the reCaptcha widget.

### How to use reCAPTCHA globally (meaning even in China):

Use `'www.recaptcha.net'` host in your code when `'www.google.com'` is not accessible.

```yaml
# config/packages/karser_recaptcha3.yaml (or app/config/config.yml if using Symfony3)

karser_recaptcha3:
    host: 'www.recaptcha.net' # default is 'www.google.com'
```

### How can I set the captcha language for different locales?

You can control the language in the small widget displayed by setting the locale in the options above.

To change the error messages, you should install the [Symfony Translation component](https://symfony.com/doc/current/translation.html).

Then replace the validation text with the translation keys for the message and messageMissingValue options:
```php
$builder->add('captcha', Recaptcha3Type::class, [
     'constraints' => new Recaptcha3 ([
         'message' => 'karser_recaptcha3.message',
         'messageMissingValue' => 'karser_recaptcha3.message_missing_value',
     ]),
]);
````
Add English, Spanish, or any other translation:
```
# translations/validators/validators.en.yaml
karser_recaptcha3.message: 'Your computer or network may be sending automated queries'
karser_recaptcha3.message_missing_value: 'The captcha value is missing'

# translations/validators/validators.es.yaml
karser_recaptcha3.message: 'Es posible que su computadora o red esté enviando consultas automatizadas'
karser_recaptcha3.message_missing_value: 'Falta el valor de captcha'
```

### How to get the ReCaptcha score:

Inject the Recaptcha3Validator and call `getLastResponse()->getScore()` after the form was submitted:
```php
<?php

use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;

class TaskController extends AbstractController
{
    public function new(Request $request, Recaptcha3Validator $recaptcha3Validator): Response
    {
        //...
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //...
            $score = $recaptcha3Validator->getLastResponse()->getScore();
            //...
        }
        //...
    }
}
```

### How to integrate re-captcha in API method:

The idea is to require the frontend to submit the captcha token, so it will be validated on server side.

First you need to add the captcha field to your transport entity:
```php
<?php

namespace App\Dto;

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
        action: 'homepage'
    }).then(function(token) {
        //the token will be sent on form submit
        $('[name="captcha"]').val(token);
        //keep in mind that token expires in 120 seconds so it's better to add setTimeout.
    });
});

//or on form post:
grecaptcha.ready(function() {
    grecaptcha.execute(siteKey, {
        action: 'homepage'
    }).then(function(token) {
        //submit the form
        return http.post(url, {email, captcha: token});
    });
});
</script>
```

### How to show errors from the captcha's response

Just add the `{{ errorCodes }}` variable to the message template:
```
$formBuilder->add('captcha', Recaptcha3Type::class, [
    'constraints' => new Recaptcha3(['message' => 'There were problems with your captcha. Please try again or contact with support and provide following code(s): {{ errorCodes }}']),
])
```

### How to deal with functional and e2e testing:

Recaptcha won't allow you to test your app efficiently unless you disable it for the environment you are testing against.

```yaml
# app/config/config.yml (or config/packages/karser_recaptcha3.yaml if using Symfony4)
karser_recaptcha3:
    enabled: '%env(bool:RECAPTCHA3_ENABLED)%'
```

```bash
#.env.test or an environment variable
RECAPTCHA3_ENABLED=0
```

### How to set the threshold from PHP dynamically rather from the .yaml config or .env?

You should inject `@karser_recaptcha3.google.recaptcha` in your service and call `setScoreThreshold` method.
```yaml
#services.yaml
App\Services\YourService:
    arguments: ['@karser_recaptcha3.google.recaptcha']
```

```php
#App/Services/YourService.php

use ReCaptcha\ReCaptcha;

class YourService {
    private $reCaptcha;

    public function __construct(ReCaptcha $reCaptcha) {
        $this->reCaptcha = $reCaptcha;
    }

    public function yourMethod() {
        $this->reCaptcha->setScoreThreshold(0.7);
    }
}
```

### How to resolve IP propertly when behind Cloudflare:

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

### Symfony HttpClient integration

If you have a dependency on `symfony/http-client` in your application then it will be automatically wired
to use via `RequestMethod/SymfonyHttpClient`.

Troubleshooting checklist
-------------------------

### Make sure you setup recaptcha key/secret of version 3.
Also, make sure you added the domain you use in the [recaptcha settings](https://www.google.com/recaptcha/admin).
Usually dev domain differs from the production one, so better to double check.
![image](https://user-images.githubusercontent.com/1675033/71197630-bbd7a000-229a-11ea-9421-7205d2e6f52c.png)

### Make sure you are seeing this in the html of your rendered form
```
<input type="hidden" id="form_captcha" name="form[captcha]" /><script>
    var recaptchaCallback_form_captcha = function() {
    grecaptcha.execute('<YOUR-RECAPTCHA-KEY>', {action: 'landing'}).then(function(token) {
    document.getElementById('form_captcha').value = token;
    });
    };
    </script><script src="https://www.google.com/recaptcha/api.js?render=<YOUR-RECAPTCHA-KEY>&onload=recaptchaCallback_form_captcha" async defer></script> 
</form>
```

### Make sure you don't have javascript errors in the browser console


Testing
-------

```
composer update
vendor/bin/phpunit
```


