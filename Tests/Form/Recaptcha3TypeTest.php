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

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();
        $this->assertSame(self::SITEKEY, $view->vars['site_key']);
        $this->assertSame(self::HOST, $view->vars['host']);
        $this->assertSame('homepage', $view->vars['action_name']);
        $this->assertSame('', $view->vars['script_nonce_csp']);
        $this->assertTrue($view->vars['enabled']);
    }
}
