<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Tests\fixtures\RecaptchaMock;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FunctionalTest extends TestCase
{
    /** @var TestKernel */
    private $kernel;

    public function setUp()
    {
        $this->kernel = new TestKernel(uniqid(), false);
    }

    public function testFormJavascriptPresent_ifEnabled()
    {
        //GIVEN
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/default.yml');
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $formFactory = $container->get('form.factory');
        $twig = $container->get('twig');

        $form = $this->createContactForm($formFactory);

        $template = $twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertContains('<input type="hidden" id="form_captcha" name="form[captcha]" />', $view);
        self::assertContains('<script src="https://www.google.com/recaptcha/api.js?render=key&onload=recaptchaCallback_form_captcha" async defer></script>', $view);
        self::assertContains("document.getElementById('form_captcha').value = token;", $view);
    }

    public function testFormJavascriptAbsent_ifDisabled()
    {
        //GIVEN
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/disabled.yml');
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $formFactory = $container->get('form.factory');
        $twig = $container->get('twig');

        $form = $this->createContactForm($formFactory);
        $template = $twig->createTemplate('{{ form_widget(form) }}');
        //WHEN
        $view = $template->render(['form' => $form->createView()]);

        //THEN
        self::assertContains('<input type="hidden" id="form_captcha" name="form[captcha]" />', $view);
        self::assertNotContains('<script src="https://www.google.com/recaptcha/api.js?render=key"></script>', $view);
        self::assertNotContains("document.getElementById('form_captcha').value = token;", $view);
    }

    public function testFormValid_ifEnabled()
    {
        //GIVEN
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/default.yml');
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $formFactory = $container->get('form.factory');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = true;

        //WHEN
        $form = $this->createContactForm($formFactory);

        //THEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testFormInvalid_ifCaptchaFails()
    {
        //GIVEN
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/default.yml');
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $formFactory = $container->get('form.factory');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($formFactory);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        //THEN
        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
        self::assertSame('Your computer or network may be sending automated queries',
            $form->getErrors()[0]->getMessage());
    }

    public function testFormValid_ifCaptchaFails_butDisabled()
    {
        //GIVEN
        $this->kernel->setConfigurationFilename(__DIR__ . '/fixtures/config/disabled.yml');
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $formFactory = $container->get('form.factory');

        /** @var RecaptchaMock $recaptchaMock */
        $recaptchaMock = $container->get('karser_recaptcha3.google.recaptcha');
        $recaptchaMock->nextSuccess = false;

        $form = $this->createContactForm($formFactory);

        //WHEN
        $form->submit(['name' => 'John', 'captcha' => 'token']);
        //THEN
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    private function createContactForm(FormFactoryInterface $formFactory)
    {
        return $formFactory->createBuilder(FormType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
            ])
            ->getForm();
    }
}
