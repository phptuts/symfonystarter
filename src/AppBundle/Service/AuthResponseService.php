<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Model\Response\ResponseAuthenticationModel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CredentialResponseBuilderService
 * @package AppBundle\Service\Credential
 */
class AuthResponseService
{
    /***
     * @var string the name of the cookie used to store the auth token.
     */
    const AUTH_COOKIE = 'auth_cookie';

    /**
     * @var JWSService
     */
    private $JWSService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * CredentialResponseBuilderService constructor.
     * @param JWSService $JWSService
     * @param UserService $userService
     */
    public function __construct(JWSService $JWSService, UserService $userService)
    {
        $this->JWSService = $JWSService;
        $this->userService = $userService;
    }

    /**
     * Creates a json response that will contain new credentials for the user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function createAuthResponse(User $user)
    {
        $user = $this->userService->updateUserRefreshToken($user);
        $responseModel = $this->createResponseAuthModel($user);

        return $this->createResponse($responseModel);
    }

    /**
     * Creates a credentials model for the user
     *
     * @param User $user
     * @return ResponseAuthenticationModel
     */
    private function createResponseAuthModel(User $user)
    {
        $authTokenModel = $this->JWSService->createAuthTokenModel($user);

        return new ResponseAuthenticationModel($user, $authTokenModel, $user->getAuthRefreshModel());
    }

    /**
     * @param ResponseAuthenticationModel $responseModel
     *
     * @return JsonResponse
     */
    private function createResponse(ResponseAuthenticationModel $responseModel)
    {
        $response = new JsonResponse($responseModel->getBody(), Response::HTTP_CREATED);
        $response->headers->setCookie(
            new Cookie(
                self::AUTH_COOKIE,
                $responseModel->getAuthToken(),
                $responseModel->getTokenExpirationTimeStamp(),
                null,
                false,
                false
            )
        );

        return $response;
    }
}