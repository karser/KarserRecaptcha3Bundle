<?php

namespace Services;

use Karser\Recaptcha3Bundle\Services\HostProvider;
use PHPUnit\Framework\TestCase;

class HostProviderTest extends TestCase
{

    public function test__construct()
    {
        $hostProvider = new HostProvider('www.a-domain.tld');
        self::assertSame('www.a-domain.tld', $hostProvider->getHost());
    }

    public function testGetHost()
    {
        $hostProvider = new HostProvider('www.a-domain.tld');
        self::assertEquals('www.a-domain.tld', $hostProvider->getHost());
    }

    public function testGetVerifyUrl()
    {
        $hostProvider = new HostProvider('www.a-domain.tld');
        self::assertEquals('https://www.a-domain.tld/recaptcha/api/siteverify', $hostProvider->getVerifyUrl());
    }
}
