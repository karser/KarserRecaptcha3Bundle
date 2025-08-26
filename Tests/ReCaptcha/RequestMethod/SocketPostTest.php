<?php declare(strict_types=1);

/**
 * This is a PHP library that handles calling reCAPTCHA.
 *
 * BSD 3-Clause License
 * @copyright (c) 2019, Google Inc.
 * @link https://www.google.com/recaptcha
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Karser\Recaptcha3Bundle\Tests\ReCaptcha\RequestMethod;

use Karser\Recaptcha3Bundle\ReCaptcha\ReCaptcha;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod\SocketPost;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestParameters;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod\Socket;
use PHPUnit\Framework\TestCase;

class SocketPostTest extends TestCase
{
    public function testSubmitSuccess()
    {
        /** @var Socket&\PHPUnit\Framework\MockObject\MockObject $socket */
        $socket = $this->getMockBuilder(Socket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects(self::once())
            ->method('fsockopen')
            ->willReturn(true);
        $socket->expects(self::once())
            ->method('fwrite');
        $socket->expects(self::once())
            ->method('fgets')
            ->willReturn("HTTP/1.0 200 OK\n\nRESPONSEBODY");
        $socket->expects(self::exactly(2))
            ->method('feof')
            ->willReturnOnConsecutiveCalls(false, true);
        $socket->expects(self::once())
            ->method('fclose')
            ->willReturn(true);

        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        self::assertEquals('RESPONSEBODY', $response);
    }

    public function testOverrideSiteVerifyUrl()
    {
        /** @var Socket&\PHPUnit\Framework\MockObject\MockObject $socket */
        $socket = $this->getMockBuilder(Socket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects(self::once())
            ->method('fsockopen')
            ->with('ssl://over.ride', 443, 0, '', 30)
            ->willReturn(true);
        $socket->expects(self::once())
            ->method('fwrite')
            ->with(self::matchesRegularExpression('/^POST \/some\/path.*Host: over\.ride/s'));
        $socket->expects(self::once())
            ->method('fgets')
            ->willReturn("HTTP/1.0 200 OK\n\nRESPONSEBODY");
        $socket->expects(self::exactly(2))
            ->method('feof')
            ->willReturnOnConsecutiveCalls(false, true);
        $socket->expects(self::once())
            ->method('fclose')
            ->willReturn(true);

        $ps = new SocketPost($socket, 'https://over.ride/some/path');
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        self::assertEquals('RESPONSEBODY', $response);
    }

    public function testSubmitBadResponse()
    {
        /** @var Socket&\PHPUnit\Framework\MockObject\MockObject $socket */
        $socket = $this->getMockBuilder(Socket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects(self::once())
            ->method('fsockopen')
            ->willReturn(true);
        $socket->expects(self::once())
            ->method('fwrite');
        $socket->expects(self::once())
            ->method('fgets')
            ->willReturn("HTTP/1.0 500 NOPEn\\nBOBBINS");
        $socket->expects(self::exactly(2))
            ->method('feof')
            ->willReturnOnConsecutiveCalls(false, true);
        $socket->expects(self::once())
            ->method('fclose')
            ->willReturn(true);

        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        self::assertEquals('{"success": false, "error-codes": ["'.ReCaptcha::E_BAD_RESPONSE.'"]}', $response);
    }

    public function testConnectionFailureReturnsError()
    {
        /** @var Socket&\PHPUnit\Framework\MockObject\MockObject $socket */
        $socket = $this->getMockBuilder(Socket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects(self::once())
            ->method('fsockopen')
            ->willReturn(null);
        $ps = new SocketPost($socket);
        $response = $ps->submit(new RequestParameters("secret", "response", "remoteip", "version"));
        self::assertEquals('{"success": false, "error-codes": ["'.ReCaptcha::E_CONNECTION_FAILED.'"]}', $response);
    }
}