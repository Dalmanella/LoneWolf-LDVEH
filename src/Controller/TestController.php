<?php

namespace App\Controller;

use App\Entity\Backpack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Utilisateur;
use App\Repository\HeroRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Hero;
use App\Repository\BackpackRepository;
use App\Repository\ObjetRepository;
use App\Service\HealingOverTime;
use App\Service\IllustrationInfo;
use App\Service\Inventory;
use Symfony\Component\Validator\Constraints\Length;

class TestController extends AbstractController 
{
    private $entityManager;
    private $requestStack;
    

    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack, 
        BackpackRepository $backpackRep,
        Inventory $inventory,
        HealingOverTime $healing,
        IllustrationInfo $illustrationInfo)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;     
        $this->backpackRep = $backpackRep; 
        $this->inventory = $inventory;
        $this->healing = $healing;
        $this->illustrationInfo = $illustrationInfo;
    }
   
        
    /**
    * @Route("/test/{heroId}", name="test")
    */
    
    
        public function showTests(
            ManagerRegistry $doctrine, 
            UtilisateurRepository $UtilisateurRepository, 
            HeroRepository $heroRepository,
            $heroId=27
            ): Response
        {
            $session = $this->requestStack->getSession();  
            
        // recuperer l'Id de l'utilisateur connecté:
            /** @var Utilisateur $user */
            
            $user= $this->getUser();
            if(!empty($user))
            {
                $userId = $user->getId();
            }
        // dd($user, $id); 
            
            $utilisateurs= $UtilisateurRepository->findAll();
            // $heroes=$heroRepository->findBy( ['user_id_id', $userId] );
            
            //tous les heros du $userId
            $heroes= $this->entityManager->getRepository(Hero::class)-> findBy(['userId'=>$userId]);
            // dd($heroes);

        //déterminer l'id du heros
                // $heroId=25;
            // un hero par son id
                $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
                //dd($hero);
            
        //objets de l'inventaire du héros
                $items = $this->inventory->inventory($heroId);
            $page=1;
            $pagesVues=[];
            array_push($pagesVues, $page);
            $hero[0]->setPagesVues($pagesVues);
            $this->entityManager->flush();
        // dd($pagesVues);
            $p=25;
            $pagesVues=array_values($hero[0]->getPagesVues());
           
            $this->healing->healing($p,$pagesVues,$hero);

        //Maj pages visitées
                array_push($pagesVues, $p);
                $hero[0]->setPagesVues($pagesVues);
                $this->entityManager->flush();
                
                $idIllu=8;
                $illu = $this->illustrationInfo->illustrationInfo( $idIllu);
                
                // dd($illu);        
                
                
        //     $page=275;
        //     $pagesVues=array_values($hero[0]->getPagesVues());
        
        //     $this->healing->healing($p,$pagesVues,$hero);

        //  //Maj pages visitées
        // array_push($pagesVues, $p);
        // $hero[0]->setPagesVues($pagesVues);
        // $this->entityManager->flush();   
        
       
        
        // nombre d'objet dans l'inventaire
            $itemsLength=count($items);
        
            // test d'une arme en main
                $combatSkill = $hero[0]->getCombatSkill();
                $types=[];
            
                for ($i=0; $i<$itemsLength;$i++){
                    
                    $type=$items[$i]->getObjType();
                    array_push($types, $type);      
                }
                // dd($types);
                if(!in_array("weapon\r\n", $types)){
                    $hero[0]->setCombatSkill($combatSkill-4);
                }
        

        // tests armure
                        
                //test de présence d'un élément d'armure et ajustement de l'endurance

                
                // $endM=$hero[0]->getEndMax();
                //     for ($i=0; $i<$itemsLength;$i++){
                    
                //         $item =   $items[$i];
                //         if($item->getId()==23){
                //             // ajouter 2 à l'endurance
                //             $newEnd=$endM+2;
                            
                //             $hero[0]->setEndMax($newEnd);
                //             $hero[0]->setEndurance($newEnd);
                //             $this->entityManager->flush();

                //         }
                //         if($item->getId()==24){
                //             // ajouter 4 à l'endurance
                //             $newEnd=$endM+4;
                            
                //             $hero[0]->setEndMax($newEnd);
                //             $hero[0]->setEndurance($newEnd);
                //             $this->entityManager->flush();
                //         }
                        
                //     }
        

    return $this->render('test/test.html.twig',[
                'utilisateurs'=>$utilisateurs, 
                'items'=>$items,
                'heroes'=>$heroes, 
                'userId'=>$userId,
                'hero'=>$hero,
                'heroId'=>$heroId,
                'illu'=>$illu,
                // 'endurance'=>$endurance
                                
            ]);
                
        }

    // recuperer 1 utilisateur
        // public function showUser(ManagerRegistry $doctrine, UtilisateurRepository $UtilisateurRepository): Response
        // {
        //     $session = $this->requestStack->getSession();
        //     // $id= $session->get('session.id');
        //     $user = $UtilisateurRepository->findAll();
        //     return $this->render('test/test.html.twig',['utilisateur'=>$user,'session'=>$session]);
        // }


}