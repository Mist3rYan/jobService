<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordhasherInterface;

class UserListener{

    public function __construct(UserPasswordhasherInterface $hasher){
        $this->hasher = $hasher;
    }

    public function prePersit(User $user){
        $this->encodePassword($user);
    }
    public function preUpdate(User $user){
        $this->encodePassword($user);
    }

    //Encode password
    public function encodePassword(User $user){
        if($user->getPlainPassword()=== null){
            return;
        }
        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        )
        $user->setPlainPassword(null);
    }
}