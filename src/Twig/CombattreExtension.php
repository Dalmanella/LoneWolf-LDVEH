<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
//use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\CombatEnd;

class CombattreExtension extends AbstractExtension
{
    // public function getFilters(): array
    // {
    //     return [
    //         // If your filter generates SAFE HTML, you should add a third
    //         // parameter: ['is_safe' => ['html']]
    //         // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
    //         new TwigFilter('Combattre', [$this, 'combattreFunction']),
    //     ];
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('combattre', [$this, 'combattreFunction']),
        ];
    }

    // public function doSomething($value)
    // {
    //     // ...
    // }

    public function combattreFunction ($entityManager,$hero,$combatTables,$CR,$fightId) 
    {
        $fight= $entityManager->getRepository(CombatEnd::class)->findById($fightId);
                    //recup de l'endurance des adversaires                                      
                    $endA=$fight[0]->getEndE();
                    $endLw=$fight[0]->getEndL();
                    
                    // tirage du degré de réussite de l'action de combat
                    $coup = rand(0,9); 
                   
                    //résultat de l'action sur les tables de combat                      
                    $combatTables->CombatTables($CR,$fight,$endA,$endLw,$coup);
                    
                    // tour de combat
                    $tour = $fight[0]->getTour();
                    $fight[0]->setTour($tour+1);

                    // mise à zéro si l'endurance d'un adversaire tombe en dessous de zéro 
                        if($fight[0]->getEndE() < 0){
                            $fight[0]->setEndE(0);
                        }
                        if($fight[0]->getEndL() < 0){
                            $fight[0]->setEndL(0);
                        }
                        $entityManager->persist($fight[0]);
                        $entityManager->flush();

                    // maj de l'endurance du hero dans sa fiche hero                
                    $hero[0]->setEndurance($fight[0]->getEndL());
                    $entityManager->persist($hero[0]);
                    $entityManager->flush();

                    return $fight;
    }


}
