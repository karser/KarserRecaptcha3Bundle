<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\Validator\Constraints;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use Karser\Recaptcha3Bundle\Tests\fixtures\RecaptchaMock;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class Recaptcha3ValidatorTest extends ConstraintValidatorTestCase
{
    /** @var IpResolverInterface|MockObject */
    private $resolver;
    /** @var RecaptchaMock */
    private $recaptcha;

    public function setUp(): void
    {
        $this->resolver = $this->getMockBuilder(IpResolverInterface::class)->getMock();
        parent::setUp();
    }

    protected function createValidator()
    {
        $this->recaptcha = new RecaptchaMock();
        return new Recaptcha3Validator($this->recaptcha, $enabled = true, $this->resolver);
    }

    public function testValidIfNotEnabled()
    {
        $validator = new Recaptcha3Validator($this->recaptcha, $enabled = false, $this->resolver);
        $this->recaptcha->nextSuccess = false;

        $validator->validate('test', new Recaptcha3());
        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new Recaptcha3());
    }

    public function testValidCase()
    {
        $this->recaptcha->nextSuccess = true;
        $this->validator->validate('test', new Recaptcha3());
        $this->assertNoViolation();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function testInvalidCase($testToken)
    {
        $this->recaptcha->nextSuccess = false;
        $this->recaptcha->nextErrorCodes = ['test1', 'test2'];
        $this->validator->validate($testToken, new Recaptcha3(['message' => 'myMessage']));

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"'.$testToken.'"')
            ->setParameter('{{ errorCodes }}', '"test1; test2"')
            ->setCode(Recaptcha3::INVALID_FORMAT_ERROR)
            ->assertRaised();
    }

    public static function invalidTokensProvider()
    {
        return [
            ['invalid-token'],
        ];
    }

    /**
     * @dataProvider emptyTokensProvider
     */
    public function testEmptyCase($testToken)
    {
        $this->recaptcha->nextSuccess = false;
        $this->validator->validate($testToken, new Recaptcha3(['messageMissingValue' => 'messageMissingValue']));

        $this->buildViolation('messageMissingValue')
            ->setParameter('{{ value }}', '"'.$testToken.'"')
            ->setParameter('{{ errorCodes }}', '""')
            ->setCode(Recaptcha3::INVALID_FORMAT_ERROR)
            ->assertRaised();
    }

    public static function emptyTokensProvider()
    {
        return [
            [''],
            [null],
        ];
    }

    public function testLastResponse()
    {
        $this->recaptcha->nextSuccess = true;

        $validator = new Recaptcha3Validator($this->recaptcha, $enabled = true, $this->resolver);
        self::assertNull($this->validator->getLastResponse());

        $validator->validate('test', new Recaptcha3());
        self::assertNotNull($validator->getLastResponse());
    }

    public function testGetErrorName()
    {
        $constraint = new Recaptcha3();

        self::assertSame('INVALID_FORMAT_ERROR', $constraint::getErrorName('7147ffdb-0af4-4f7a-bd5e-e9dcfa6d7a2d'));
    }

    public function testThrowException()
    {
        $constraint = $this->createMock(Constraint::class);

        self::expectException(UnexpectedTypeException::class);
        $this->validator->validate('invalid-token', $constraint);
    }
}
