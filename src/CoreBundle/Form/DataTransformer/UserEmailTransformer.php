<?php

namespace CoreBundle\Form\DataTransformer;

use CoreBundle\Entity\User;
use CoreBundle\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * This transformer is used to transform the whole form.
 *
 * Class UserEmailTransformer
 * @package CoreBundle\Form\DataTransformer
 */
class UserEmailTransformer implements DataTransformerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * This always return null or the full object because we transforming the whole form with this transformer
     *
     * @param User $user
     * @return string
     */
    public function transform($user)
    {
        if (empty($user)) {
            return new User();
        }

        return $user;

    }

    /**
     * This will always receive a full user object because we are transforming the whole form
     * If the we can't find the user we throw a transformation exception
     * @param User $user
     *
     * @return User
     */
    public function reverseTransform($user)
    {
        // If the email field is null we return null
        if (empty($user->getEmail())) {
            return null;
        }

        $user = $this->userRepository->findUserByEmail($user->getEmail());

        if (null === $user) {
            throw new TransformationFailedException('Email was not found.');
        }

        return $user;
    }

}