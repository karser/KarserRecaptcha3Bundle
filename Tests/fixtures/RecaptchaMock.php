<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\fixtures;

use ReCaptcha\Response;

class RecaptchaMock
{
    public $nextSuccess = true;
    public $nextErrorCodes = [];

    public function verify($response, $remoteIp = null)
    {
        return new Response($this->nextSuccess, $this->nextErrorCodes);
    }
}
