<?php


namespace AppBundle\Listener;


use AppBundle\Event\UserEvent;
use AppBundle\Service\EmailService;

class UserListener
{
    /**
     * @var EmailService
     */
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handles the user registration
     *
     * @param UserEvent $userEvent
     */
    public function onRegister(UserEvent $userEvent)
    {
        $this->emailService->sendRegisterEmail($userEvent->getUser());
    }

    /**
     * Handles forget password
     *
     * @param UserEvent $userEvent
     */
    public function onForgetPassword(UserEvent $userEvent)
    {
        $this->emailService->sendForgetPasswordEmail($userEvent->getUser());
    }
}