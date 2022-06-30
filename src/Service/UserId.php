<?php
namespace App\Service;


class UserId {

    public function getUserId() : int
    {

        // recuperer l'Id de l'utilisateur connecté:
            /** @var Utilisateur $user */
            
            $user= $this->getUser();
            if(!empty($user))
            {
                $userId = $user->getId();
            }
            
        
        return $userId ;
    }

}