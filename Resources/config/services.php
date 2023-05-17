<?php

declare(strict_types=1);

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Services\HostProvider;
use Karser\Recaptcha3Bundle\Services\IpResolver;
use Karser\Recaptcha3Bundle\RequestMethod\SymfonyHttpClient;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\Curl;
use ReCaptcha\RequestMethod\CurlPost;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\ExpressionLanguage\Expression;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('karser_recaptcha3.host_provider', HostProvider::class)
        ->args(['%karser_recaptcha3.host%']);

    $services->set('karser_recaptcha3.form.type', Recaptcha3Type::class)
        ->private()
        ->args([
            '%karser_recaptcha3.site_key%',
            '%karser_recaptcha3.host%',
            '%karser_recaptcha3.enabled%'
        ])
        ->tag('form.type', []);

    $services->set('karser_recaptcha3.validator', Recaptcha3Validator::class)
        ->private()
        ->args([
            new ReferenceConfigurator('karser_recaptcha3.google.recaptcha'),
            '%karser_recaptcha3.enabled%',
            new ReferenceConfigurator('karser_recaptcha3.ip_resolver')]
        )
        ->tag('validator.constraint_validator', ['alias' => 'karser_recaptcha3_validator']);

    $services->alias(Recaptcha3Validator::class, 'karser_recaptcha3.validator');

    $services->set('karser_recaptcha3.ip_resolver', IpResolver::class)
        ->private()
        ->args([new ReferenceConfigurator('request_stack')]);

    $services->set('karser_recaptcha3.google.recaptcha', ReCaptcha::class)
        ->arg('$secret', '%karser_recaptcha3.secret_key%')
        ->arg('$requestMethod', new ReferenceConfigurator('karser_recaptcha3.google.request_method'))
        ->call('setScoreThreshold', ['%karser_recaptcha3.score_threshold%']);

    $services->alias('karser_recaptcha3.google.request_method', 'karser_recaptcha3.google.request_method.curl_post');

    $services->set('karser_recaptcha3.google.request_method.curl_post', CurlPost::class)
        ->args([
            new ReferenceConfigurator('karser_recaptcha3.google.request_method.curl'),
            new Expression("service('karser_recaptcha3.host_provider').getVerifyUrl()")
        ]);

    $services->set('karser_recaptcha3.google.request_method.curl', Curl::class);

    $services->set('karser_recaptcha3.request_method.symfony_http_client', SymfonyHttpClient::class)
        ->args([
            (new ReferenceConfigurator('http_client'))->ignoreOnInvalid(),
            new Expression("service('karser_recaptcha3.host_provider').getVerifyUrl()")
        ]);
};
