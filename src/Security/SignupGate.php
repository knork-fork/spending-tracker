<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Decides whether /signup may be used, based on the INVITE_SECRET env var:
 * empty = open, "disabled" = off, anything else = invite required.
 */
final class SignupGate
{
    public function __construct(
        #[Autowire(param: 'app.invite_secret')]
        private readonly string $inviteSecret,
    ) {
    }

    public function state(): SignupState
    {
        return match ($this->inviteSecret) {
            '' => SignupState::Open,
            'disabled' => SignupState::Disabled,
            default => SignupState::InviteOnly,
        };
    }

    public function allows(?string $invite): bool
    {
        return match ($this->state()) {
            SignupState::Open => true,
            SignupState::Disabled => false,
            SignupState::InviteOnly => $invite !== null && hash_equals($this->inviteSecret, $invite),
        };
    }
}
