<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Tests\fixtures\RecaptchaMock;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Constraints\NotBlank;

class FunctionalTest extends TestCase
{
    /** @var TestKernel */
    private $kernel;

    private $formFactory;
    private $twig;

    public function setUp(): void
    {
        $this->kernel = new TestKernel(uniqid(), false);
    }

    public function testFormJavascriptPresent_ifEnabled()
    {
        //GIVEN
        $this->bootKernel('default.yml');
        $form = $this->createContactForm($this->formFactory);

        $template = $this->twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertStringContainsString('<input type="hidden" id="form_captcha" name="form[captcha]" />', $view);
        self::assertStringContainsString('<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render=key&hl=en&onload=recaptchaCallback_form_captcha" async defer nonce=""></script>', $view);
        self::assertStringContainsString('var recaptchaCallback_form_captcha', $view);
        self::assertStringContainsString("document.getElementById('form_captcha').value = token;", $view);
    }

    public function testHyphenConvertedToUnderscore()
    {
        //GIVEN
        $this->bootKernel('default.yml');
        $form = $this->createContactForm($this->formFactory, [], 'capt-cha');

        $template = $this->twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertStringContainsString('<input type="hidden" id="form_capt-cha" name="form[capt-cha]" />', $view);
        self::assertStringContainsString('<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render=key&hl=en&onload=recaptchaCallback_form_capt_cha" async defer nonce=""></script>', $view);
        self::assertStringContainsString('var recaptchaCallback_form_capt_cha', $view);
        self::assertStringContainsString("document.getElementById('form_capt-cha').value = token;", $view);
    }

    public function testFormJavascriptAbsent_ifDisabled()
    {
        //GIVEN
        $this->bootKernel('disabled.yml');

        $form = $this->createContactForm($this->formFactory);
        $template = $this->twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertStringContainsString('<input type="hidden" id="form_captcha" name="form[captcha]" />', $view);
        self::assertStringNotContainsString('<script src="https://www.google.com/recaptcha/api.js?render=key"></script>', $view);
        self::assertStringNotContainsString("document.getElementById('form_captcha').value = token;", $view);
    }

    public function testFormValid_ifEnabled()
    {
        //GIVEN
        $container = $this->bootKernel('default.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = true;

        //WHEN
        $form = $this->createContactForm($this->formFactory);

        //THEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testFormInvalid_ifCaptchaFails()
    {
        //GIVEN
        $container = $this->bootKernel('default.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;
        $recaptchaMock->nextErrorCodes = ['test1', 'test2'];

        $form = $this->createContactForm($this->formFactory, ['message' => 'Error: {{ errorCodes }}']);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        //THEN
        $this->assertFormHasCaptchaError($form, 'Error: "test1; test2"');
    }

    public function testFormInvalid_ifCaptchaEmpty()
    {
        //GIVEN
        $container = $this->bootKernel('default.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($this->formFactory);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => '']);
        //THEN
        $this->assertFormHasCaptchaError($form, 'The captcha value is missing');
    }

    public function testFormInvalid_ifCaptchaNull()
    {
        //GIVEN
        $container = $this->bootKernel('default.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($this->formFactory);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => null]);
        //THEN
        $this->assertFormHasCaptchaError($form, 'The captcha value is missing');
    }

    public function testFormInvalid_ifCaptchaUndefined()
    {
        //GIVEN
        $container = $this->bootKernel('default.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($this->formFactory);

        //WHEN
        $form->submit(['name' => 'John']);
        //THEN
        $this->assertFormHasCaptchaError($form, 'The captcha value is missing');
    }

    public function testFormValid_ifCaptchaFails_butDisabled()
    {
        //GIVEN
        $container = $this->bootKernel('disabled.yml');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($this->formFactory);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        //THEN
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testFormJavascriptNoncePresent_ifSet()
    {
        //GIVEN
        $this->bootKernel('default.yml');
        $form = $this->createContactForm($this->formFactory, [], null, 'csp_nonce');

        $template = $this->twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertStringContainsString('<input type="hidden" id="form_captcha" name="form[captcha]" />', $view);
        self::assertStringContainsString('<script type="text/javascript" nonce="csp_nonce">', $view);
        self::assertStringContainsString('<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render=key&hl=en&onload=recaptchaCallback_form_captcha" async defer nonce="csp_nonce"></script>', $view);
        self::assertStringContainsString('var recaptchaCallback_form_captcha', $view);
        self::assertStringContainsString("document.getElementById('form_captcha').value = token;", $view);
    }

    public function testFormJavascriptAltHostIsPreserved_ifSet()
    {
        //GIVEN
        $this->bootKernel('alt_host.yml');
        $form = $this->createContactForm($this->formFactory, [], null);

        $template = $this->twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertStringContainsString('<script type="text/javascript" src="https://www.recaptcha.net/recaptcha/api.js?render=key&hl=en&onload=recaptchaCallback_form_captcha" async defer nonce=""></script>', $view);
    }

    public function testUsesSymfonyHttpClient()
    {
        if (Kernel::VERSION_ID < 50200) {
            self::markTestSkipped('skip');
        }

        $this->bootKernel('http_client.yml');

        $form = $this->createContactForm($this->formFactory);
        $form->submit(['name' => 'John', 'captcha' => 'token']);

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    private function assertFormHasCaptchaError(FormInterface $form, string $expectedMessage)
    {
        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
        self::assertSame($expectedMessage, $form->getErrors()[0]->getMessage());
    }

    private function bootKernel(string $config): ContainerInterface
    {
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/'.$config);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $this->formFactory = $container->get('form.factory.public');
        $this->twig = $container->get('twig.public');

        return $container;
    }

    private function createContactForm(FormFactoryInterface $formFactory, array $constraintParams = [], ?string $captchaId = null, ?string $nonce = null)
    {
        return $formFactory->createBuilder(FormType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add($captchaId ?? 'captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3($constraintParams),
                'script_nonce_csp' => $nonce
            ])
            ->getForm();
    }
}
