<?php
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
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod\Curl;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod\CurlPost;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestParameters;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Karser\Recaptcha3Bundle\Tests\ReCaptcha\RequestMethod\CurlPost
 */
class CurlPostTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('curl')) {
            self::markTestSkipped('The cURL extension is not available.');
        }
    }

    public function testSubmit(): void
    {
        $curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curl->expects(self::once())
            ->method('init')
            ->willReturn(new \stdClass());

        $curl->expects(self::once())
            ->method('setoptArray')
            ->willReturn(true);

        $curl->expects(self::once())
            ->method('exec')
            ->willReturn('RESPONSEBODY');

        $curl->expects(self::once())
            ->method('close');

        $pc = new CurlPost($curl);
        $response = $pc->submit(new RequestParameters("secret", "response"));
        self::assertEquals('RESPONSEBODY', $response);
    }

    public function testOverrideSiteVerifyUrl(): void
    {
        $url = 'OVERRIDE';

        $curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curl->expects(self::once())
            ->method('init')
            ->with($url)
            ->willReturn(new \stdClass());

        $curl->expects(self::once())
            ->method('setoptArray')
            ->willReturn(true);

        $curl->expects(self::once())
            ->method('exec')
            ->willReturn('RESPONSEBODY');

        $curl->expects(self::once())
            ->method('close');

        $pc = new CurlPost($curl, $url);
        $response = $pc->submit(new RequestParameters("secret", "response"));
        self::assertEquals('RESPONSEBODY', $response);
    }

    public function testConnectionFailureReturnsError(): void
    {
        $curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curl->expects(self::once())
            ->method('init')
            ->willReturn(new \stdClass());

        $curl->expects(self::once())
            ->method('setoptArray')
            ->willReturn(true);

        $curl->expects(self::once())
            ->method('exec')
            ->willReturn(false);

        $curl->expects(self::once())
            ->method('close');

        $pc = new CurlPost($curl);
        $response = $pc->submit(new RequestParameters("secret", "response"));
        self::assertEquals('{"success": false, "error-codes": ["'.ReCaptcha::E_CONNECTION_FAILED.'"]}', $response);
    }
}