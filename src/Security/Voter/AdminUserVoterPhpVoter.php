<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdminUserVoterPhpVoter extends Voter
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    // basically, the supports() method will be called every time the security system is called.
    // The first argument will be something like ROLE_ADMIN or, in our case, ADMIN_USER_EDIT.
    // And also, in our case,
    // $subject will be the User object.
    // Our job is to return true in that situation.
    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['ADMIN_USER_EDIT'])
            && $subject instanceof User;
    }

    // That's it! Now, when the security system calls supports(), if we return true, then Symfony will call voteOnAttribute().
    // Our job there is simply to return true or false based on whether or not the current user should have access to this User object in the admin.
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof User) {
            throw new \LogicException('Subject is not instace of User,');
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'ADMIN_USER_EDIT':
                // logic to determine if the user can EDIT
                return $user === $subject || $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return false;
    }
}
