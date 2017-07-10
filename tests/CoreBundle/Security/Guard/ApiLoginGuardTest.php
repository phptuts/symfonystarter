<?php

namespace Tests\CoreBundle\Security\Guard;

use CoreBundle\Entity\User;
use CoreBundle\Security\Guard\ApiLoginGuard;
use CoreBundle\Service\Credential\CredentialResponseBuilderService;
use Mockery\Mock;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Tests\BaseTestCase;

class ApiLoginGuardTest extends BaseTestCase
{
    /**
     * @var ApiLoginGuard|Mock
     */
    protected $apiLoginGuard;

    /**
     * @var EncoderFactory|Mock
     */
    protected $encoderFactory;

    /**
     * @var CredentialResponseBuilderService|Mock
     */
    protected $credentialResponseBuilderService;

    public function setUp()
    {
        parent::setUp();
        $this->encoderFactory = \Mockery::mock(EncoderFactory::class);
        $this->credentialResponseBuilderService = \Mockery::mock(CredentialResponseBuilderService::class);

        $this->apiLoginGuard = new ApiLoginGuard($this->encoderFactory, $this->credentialResponseBuilderService);
    }

    /**
     * Tests that the service definition is setup right
     */
    public function testServiceDefinition()
    {
        Assert::assertInstanceOf(ApiLoginGuard::class, $this->getContainer()->get('startsymfony.core.security.api_login_guard'));
    }

    /**
     * This tests a bunch of invalid requests
     * @param Request $request
     * @dataProvider dataProviderForGetCreds
     */
    public function testGetCredentialsOnBadRequests(Request $request)
    {
        Assert::assertNull($this->apiLoginGuard->getCredentials($request));
    }

    public function testValidGetCredentials()
    {
        $request = Request::create('/api/login_check', 'POST', [],[],[],[], json_encode(['password' => 'asdfasdf', 'email' => 'blue@gmailcom']));
        $request->attributes->set('_route', 'api_login');

        $creds = $this->apiLoginGuard->getCredentials($request);

        Assert::assertEquals('asdfasdf', $creds['password']);
        Assert::assertEquals('blue@gmailcom', $creds['email']);
    }

    public function testGetUser()
    {
        $user = new User();
        $userProvider = \Mockery::mock(UserProviderInterface::class);
        $userProvider->shouldReceive('loadUserByUsername')->with('blue@gmail.com')->andReturn($user);

        $userFound =  $this->apiLoginGuard->getUser(['email' => 'blue@gmail.com'], $userProvider);

        Assert::assertEquals($user, $userFound);
    }

    public function testCheckCredentials()
    {
        $user =new User();
        $user->setPassword('adsfasdfasdf');
        $encoder = \Mockery::mock(PasswordEncoderInterface::class);
        $encoder->shouldReceive('isPasswordValid')->with('adsfasdfasdf', 'password', null)->andReturn(true);

        $this->encoderFactory->shouldReceive('getEncoder')->andReturn($encoder);

        Assert::assertTrue($this->apiLoginGuard->checkCredentials(['password' => 'password'],$user));
    }

    public function testOnAuthenticationSuccess()
    {
        $user = new User();
        $response = \Mockery::mock(JsonResponse::class);
        $token = \Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')->andReturn($user);

       $this->credentialResponseBuilderService->shouldReceive('createCredentialResponse')->with($user)->andReturn($response);
       $request = Request::create('/api/login_check', 'POST', [],[],[],[], json_encode(['type' => 'google', 'token' => null]));

       $responseReturned = $this->apiLoginGuard->onAuthenticationSuccess($request, $token, 'main');

       Assert::assertEquals($response, $responseReturned);
    }


    public function dataProviderForGetCreds()
    {
        $request = Request::create('/api/login_check', 'POST', [],[],[],[], json_encode(['type' => 'google', 'token' => null]));
        $request->attributes->set('_route', 'api_login');


        $request2 = Request::create('/api/login_check', 'POST', [],[],[],[], json_encode(['email' => 'asdfasdf']));
        $request2->attributes->set('_route', 'api_login');


        $request3 = Request::create('/api/login_check', 'POST', [],[],[],[], json_encode(['password' => 'asdfasdf']));
        $request3->attributes->set('_route', 'api_login');

        return [
            [Request::create('/bad_end_point', 'POST')],
            [Request::create('/token_login_check', 'GET')],
            [$request],
            [$request2],
            [$request3]
        ];
    }
}