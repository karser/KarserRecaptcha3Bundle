<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Recaptcha3Type extends AbstractType
{
    /** @var string */
    private $siteKey;

    /** @var string */
    private $host;

    /** @var bool */
    private $enabled;

    public function __construct(string $siteKey, string $host, bool $enabled)
    {
        $this->siteKey = $siteKey;
        $this->host = $host;
        $this->enabled = $enabled;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['site_key'] = $this->siteKey;
        $view->vars['host'] = $this->host;
        $view->vars['enabled'] = $this->enabled;
        $view->vars['action_name'] = $options['action_name'];
        $view->vars['script_nonce_csp'] = $options['script_nonce_csp'] ?? '';
        $view->vars['locale'] = $options['locale'] ?? 'en';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'karser_recaptcha3';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'site_key' => null,
            'host' => null,
            'action_name' => 'homepage',
            'locale' => 'en',
            'script_nonce_csp' => '',
        ]);
    }
}
