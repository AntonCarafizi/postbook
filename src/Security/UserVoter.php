<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var User $user */
        $user = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $token);
            case self::EDIT:
                return $this->canEdit($user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(User $user, TokenInterface $token)
    {
        // if they can edit, they can view
        if ($this->canEdit($user, $token)) {
            return true;
        }

        // the Post object could have, for example, a method `isPrivate()`
        //return !$post->isPrivate();
    }

    private function canEdit(User $user, TokenInterface $token)
    {
        // this assumes that the Post object has a `getOwner()` method
        return $user === $token->getUser();
    }
}