<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
//use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Hero;

class FallExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fall', [$this, 'fallFunction']),
        ];
    }

    public function fallFunction ($entityManager,$heroId,$hit) 
    {
        $hero=$entityManager->getRepository(Hero::class)->findById($heroId);
        $end = $hero[0]->getEndurance();
        
        $newEnd = $end - $hit ;
        

        $hero[0] ->setEndurance($newEnd);

        $entityManager->persist($hero[0]);
        $entityManager->flush($hero[0]);
        
        return $hero;
    }


}
