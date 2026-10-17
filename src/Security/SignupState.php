<?php

declare(strict_types=1);

namespace App\Security;

enum SignupState
{
    /** Signup is open to anyone. */
    case Open;
    /** Signup requires a valid ?invite=<secret>. */
    case InviteOnly;
    /** Signup is turned off entirely. */
    case Disabled;
}
