<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\Services;

use Karser\Recaptcha3Bundle\Services\IpResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class IpResolverTest extends TestCase
{
    public function testEmptyRequest()
    {
        $stack = new RequestStack();
        $stack->push(new Request());
        $resolver = new IpResolver($stack);
        self::assertNull($resolver->resolveIp());
    }

    public function testRequest()
    {
        $stack = new RequestStack();
        $stack->push(new Request([], [], [], [], [], ['REMOTE_ADDR' => '0.0.0.0']));
        $resolver = new IpResolver($stack);
        self::assertSame('0.0.0.0', $resolver->resolveIp());
    }
}
