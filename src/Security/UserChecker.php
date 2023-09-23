<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(private ParameterBagInterface $parameterBag)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        return;
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($this->parameterBag->get('app.registration.email_verification.enabled') && !$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('please verify your email address');
        }
    }
}