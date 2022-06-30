<?php
namespace App\Service;


class RollDice {

    public function rollDice() : int
    {
        $value=rand(0,9);
        return $value ;
    }

}