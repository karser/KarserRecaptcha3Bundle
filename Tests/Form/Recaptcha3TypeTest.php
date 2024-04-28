<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\Form;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class Recaptcha3TypeTest extends TypeTestCase
{
    const SITEKEY = '<sitekey>';
    const HOST = '<host>';

    protected function getExtensions()
    {
        $type = new Recaptcha3Type(self::SITEKEY, self::HOST, true);

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testDefaultOptions()
    {
        $data = '<captcha-token>';

        $form = $this->factory->create(Recaptcha3Type::class);
        $form->setData($data);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($data, $form->getData());

        $view = $form->createView();
        self::assertSame(self::SITEKEY, $view->vars['site_key']);
        self::assertSame(self::HOST, $view->vars['host']);
        self::assertSame('homepage', $view->vars['action_name']);
        self::assertSame('', $view->vars['script_nonce_csp']);
        self::assertTrue($view->vars['enabled']);
    }
}
