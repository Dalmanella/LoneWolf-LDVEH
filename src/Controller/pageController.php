<?php

namespace App\Controller;

use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use App\Entity\Objet;
use App\Entity\Backpack;
use App\Entity\Ennemy;
use App\Entity\CombatEnd;

use App\Repository\BackpackRepository;

use App\Service\HealingOverTime;
use App\Service\Inventory;
use App\Service\Lunch;
use App\Service\CombatSkillCalc;
use App\Service\RollDice;
use App\Service\CombatTables;

class pageController extends AbstractController
{
    private $rollDice;
    private $requestStack;
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack, 
        RollDice $rollDice,   
        BackpackRepository $backpackRep,
        Inventory $inventory,
        HealingOverTime $healing,
        Lunch $lunch,
        CombatSkillCalc $combatSkillCalc,
        CombatTables $combatTables
    )
        {
            $this->requestStack = $requestStack;
            $this->entityManager = $entityManager;
                
            $this->rollDice = $rollDice;

            $this->backpackRep = $backpackRep; 
            $this->inventory=$inventory;
            $this->healing=$healing;
            $this->lunch=$lunch;

            $this->combatSkillCalc = $combatSkillCalc;
            $this->combatTables = $combatTables;
      
        }

        private function getInfoCombat(Int $ennemyId, String $heroId,Int $circonstance){
            //le hero par son id
            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            
            // l'ennemi
            $ennemy= $this->entityManager->getRepository(Ennemy::class)->findById($ennemyId);
            
            // nouveau combat
            $combat= new CombatEnd();
            $combat->setEndL($hero[0]->getEndurance());
            $combat->setStartEnd($hero[0]->getEndurance());
            $combat->setEndE($ennemy[0]->getEndurance());
            $this->entityManager->persist($combat);
            $this->entityManager->flush();
            $fightId= $combat->getId();
               
            // Calcul du Combat Skill
                $CS = $this->combatSkillCalc->CsCalculation($heroId,$ennemyId);
                
                    // malus / bonus spécifique au combat p283
                        if ($circonstance=283){
                            $tour= $combat->getTour();
                            if($tour < 1 ){
                                $circonstance = +2;
                            }else{
                                $circonstance = 0;
                            }
                        }    

                $CS = $CS + $circonstance;
                    
            // Calcul du Combat Ratio
                $CR = $CS - $ennemy[0]->getCombatSkill();
           
            return [
                'fightId'=> $fightId,
                'CR' => $CR,
                "CS"=> $CS,
                'ennemy'=> $ennemy                   
            ];
        }

        private function getInfoPage(Int $p, String $heroId){

            //le hero par son id
                $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            
            //pages visitées
                $pagesVues=array_values($hero[0]->getPagesVues());

            //guérir ou pas
                $heal=null;
                $pageNoHeal=[17,29,34,41,43,55,63,72,92,99,107,108,110,112,1122,116,119,133,136,1362,138,1382,145,156,158,169,170,180,1802,1803,185,188,191,192,207,208,220,227,229,231,246,253,255,260,283,336,339,340,342,351];
                if(in_array($p,$pageNoHeal,true)==true ){   
                    $heal = false;                                                
                }else{
                    $heal = true;
                }

            //soin hors combat si Healing
                if($heal == true){
                    $this->healing->healing($p,$pagesVues,$hero);
                }
            // soin p212
                if($p==212){
                    $hero[0]->setEndurance( $hero[0]->getEndMax());
                    $this->entityManager->flush();
                } 
            //perte de CS p236
                if($p==236){
                    $hero[0]->setCombatSkill( $hero[0]->getCombatSkill()-1 );
                    $this->entityManager->flush();
                } 

            //mort
                $pageDead=[351,53,54,60,108,127,154,185,219,234,259,271,286,292,306,309,327];
                if(in_array($p,$pageDead,true)==true ){
                        $hero[0]->setEndurance(0);
                        $hero[0]->setBackpack(0);
                        $this->entityManager->flush();
                }    

            //raz du goldTake
                $pageGoldRaz=[1,30,167,211,288,24,16,188,114,239,70,157,134,305];
                if(in_array($p,$pageGoldRaz,true)==true){
                    $hero[0]->setGoldTake(0);
                    $this->entityManager->flush();
                }
            //raz du isItem
                $pageItemRaz=[1,104,113];
                if(in_array($p,$pageItemRaz,true)==true){
                    $hero[0]->setIsItem(false);
                    $this->entityManager->flush();
                }

            //objets de l'inventaire du héros
                $items = $this->inventory->inventory($heroId);

            //évènements trouvailles/pertes
                // found  gold coins p33 / p269
                    if($p==33 || $p==269) {
                        if($p==33){$coins=3;};
                        if($p==269){$coins=10;};                                            
                        
                        if (in_array($p, $pagesVues,false)==false){
                            $gold= $hero[0]->getGold();
                            
                            if( $gold<51){
                                $newGold =  $hero[0]->setGold($gold + $coins);
                                
                                if($newGold->getGold()> 49){
                                    $hero[0]->setGold(50);
                                }
                                $this->entityManager->flush();
                            }
                        };
                    }

                //gemme brûlante p76 et 304
                    if($p==76 || $p==304) {   
                        $hero[0]->setEndurance($hero[0]->getendurance()-2);
                        $this->entityManager->flush($hero[0]);
                        $gem76=$this->entityManager->getRepository(Objet::class)-> findOneById(6);
                        $gem304=$this->entityManager->getRepository(Objet::class)-> findOneById(45);
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        if($p==76){
                            $sac[0]->addObjetId($gem76);
                        }
                        if($p==304){
                            $sac[0]->addObjetId($gem304);
                        }
                        $this->entityManager->flush($sac[0]);
                    }

                // cueillette de Laumpspur p113
                    if($p==113) {
                        $lMeal1=$this->entityManager->getRepository(Objet::class)-> findOneById(12);
                        $lMeal2=$this->entityManager->getRepository(Objet::class)-> findOneById(38);
                        
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);

                        $sac[0]->addObjetId($lMeal1);

                        $sac[0]->addObjetId($lMeal2);
                        $this->entityManager->flush($sac[0]);
                    }

                // ajout de 20 skull gems p137 
                    if($p==137) {
                        $gems=$this->entityManager->getRepository(Objet::class)-> findOneById(39);                                
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $sac[0]->addObjetId($gems);
                        $this->entityManager->flush($sac[0]);
                    }
                // ajout du pendentif Crystal Star p349 
                    if($p==349) {
                        $pendant=$this->entityManager->getRepository(Objet::class)-> findOneById(27);                                
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $sac[0]->addObjetId($pendant);
                        $this->entityManager->flush($sac[0]);
                    }

                //Golden Key p161
                    if($p==161) {
                        $key=$this->entityManager->getRepository(Objet::class)-> findOneById(1);                                
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $sac[0]->addObjetId($key);
                        $this->entityManager->flush($sac[0]);
                    }

                // perte d'or payé au marchand p262
                    if($p==262) {
                        $gold=$hero[0]->getGold();
                        $newGold=$gold-10;
                        $hero[0]->setGold($newGold);
                        $this->entityManager->flush();
                    }
                    if($p==246){
                        $gold=$hero[0]->getGold();
                        $newGold=$gold-2;
                        $hero[0]->setGold($newGold);
                        $this->entityManager->flush();
                    }

                // perte des armes et du sac à dos avec ses objets
                    if($p==162 || $p==174 || $p==205) {
                        $hero[0]->setBackpack(0); 
                        $this->entityManager->flush($hero[0]);          
                    
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $count= count($items);

                        for($i=0;$i<$count; $i++){
                            $type = $items[$i]->getObjType();                                                
                            $idItem = $items[$i]->getId();
                            $place = $items[$i]->getPlace();
                            
                            if($type == "weapon" || $place == "backpack" ){
                                $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($idItem);
                                $sac[0]->removeObjetId($objectLoose); 
                                $this->entityManager->flush($sac[0]);                                
                            }
                        }
                    } 
                    
                // perte des armes p274
                    if($p==274) {
                    $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $count= count($items);

                        for($i=0;$i<$count; $i++){
                            $type = $items[$i]->getObjType();                                                
                            $idItem = $items[$i]->getId();
                                                
                            if($type == "weapon" ){
                                $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($idItem);
                                $sac[0]->removeObjetId($objectLoose); 
                                $this->entityManager->flush($sac[0]);                                
                            }
                        }
                    }
                
                // 1 arme détruite p277
                    if($p==277) {
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        $count= count($items);
                        $weaponList=[];

                        if($hero[0]->isIsItem() != 1){

                            for($i=0;$i<$count; $i++){
                                $type = $items[$i]->getObjType();                                                
                                $idItem = $items[$i]->getId();
                                                        
                                if($type == "weapon" ){
                                    $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($idItem);
                                    array_push($weaponList,$objectLoose);
                                }
                                
                            }
                            if(count($weaponList) == 0){                        //valider si déjà pas d'arme
                                $hero[0]->setIsItem(1);
                                $this->entityManager->flush($hero[0]);
                            }
                            else if (count($weaponList) == 1){                                
                                $sac[0]->removeObjetId($objectLoose);           // destruction de l'arme si 1 seule
                                $this->entityManager->flush($sac[0]);
                                $hero[0]->setIsItem(1);
                                $this->entityManager->flush($hero[0]);
                            }
                            else{                                

                                for($i=0;$i<2;$i++){
                                    if($weaponList[$i]->getWTag() != $hero[0]->getWeapon()){
                                        //dd('je suis là', count($weaponList), $weaponList[0]->getWTag(), $hero[0]->getWeapon(), $weaponList[0]->getId() );
                                        $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($weaponList[0]->getId());
                                        $sac[0]->removeObjetId($objectLoose);          
                                        $this->entityManager->flush($sac[0]);
                                        $hero[0]->setIsItem(1);
                                        $this->entityManager->flush($hero[0]);
                                    };
                                } 

                            }
                        }
                    }
                    
                //perte d'une gemme Vordak p236
                    if($p==236) {
                    
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);

                        $count= count($items);
                        for($i=0;$i<$count; $i++){
                            if( $items[$i]->getId() == 6 && !$hero[0]->isIsItem()){
                                    $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById(6);
                                    $sac[0]->removeObjetId($objectLoose); 
                                    $this->entityManager->flush($sac[0]);   
                                    
                                    $hero[0]->setIsItem(true);
                                    $this->entityManager->flush($hero[0]);

                            }else if( $items[$i]->getId() == 45 && !$hero[0]->isIsItem()){
                                
                                $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById(45);
                                    $sac[0]->removeObjetId($objectLoose); 
                                    $this->entityManager->flush($sac[0]);

                                    $hero[0]->setIsItem(true);
                                    $this->entityManager->flush($hero[0]);
                            }
                        }        
                    }

                // perte d'or p174 et p205
                    if($p==174 || $p==205) {
                        $hero[0]->setGold(0);
                        $this->entityManager->flush();
                    }                
              
            //mémoriser la page:
                $page=$hero[0]->setPage($p);
                $this->entityManager->flush($page);           
            
            //Manger ou pas            
                $askLunch = null;
                $pageLunch = [37, 130, 147, 168, 184, 235, 300];
                if(in_array($p,$pageLunch,true)==true ){                      
                    
                    if($hero[0]->isKaihunt()== 1){
                       
                            $lunch= $hero[0]->setLunchClick(1);            // et on valide le dejeuner
                 
                            $this->entityManager->flush($lunch);
                    }         
                    $askLunch=true; 
                                                               
                }else{
                        $askLunch = false;
                }
                

            // Lunch: manger pour ne pas perdre de vie or not Lunch            
                if($askLunch == true ){
                    $isLunch= $this->lunch->Lunch($heroId);
                }else{
                    // Pas de Lunch demander
                        $hero[0]->setLunchClick(0);
                        $this->entityManager->flush();
                        $isLunch = $hero[0]->isLunchClick();
                }
            
            //Maj pages visitées    
                if (in_array($p, $pagesVues,false)==false){
                    array_push($pagesVues, $p);
                    $hero[0]->setPagesVues($pagesVues);
                    $this->entityManager->flush();
                }
            //    
            $combatPages = [351,181,188,17,29,34,43,55,63,72,112,1122,133,136,1362,138,1382,169,170,180,1802,1803,188,191,208,220,227,229,231,246,253,2532,2533,2534,255,260,2602,283,336,3362,339,340,];
            if(in_array($p,$combatPages,false)==true ){
                //    dd($p,$combatPages);
                    return [
                        'hero'=> $hero,
                        'items'=> $items,
                        'pagesVues' => $pagesVues,
                        'isLunch' => $isLunch,   
                    ];
                }else{
                    $view= $this->render('pages/page'.$p.'.html.twig',[
                        'items'=>$items, 
                        'hero'=>$hero, 
                        'heroId'=>$heroId, 
                        "p"=>$p,
                        'em'=>$this->entityManager, 
                        'isLunch'=>$isLunch,
                        'test'=> 'Je tente rien'
                    ]);

                    return [
                        'hero'=> $hero,
                        'items'=> $items,
                        'pagesVues' => $pagesVues,
                        'isLunch' => $isLunch,                
                        'view'=> $view
                    ];
                }
        }
        
/**
    * @Route("/pageIntro", name="page_intro")
    */
        public function viewIntro(Request $request) 
        {
                $p='intro';
                // recuperer l'Id de l'utilisateur connecté:
                    /** @var Utilisateur $user */
                    $user= $this->getUser();
                    if(!empty($user)) { $userId = $user->getId(); };     
                    // dd($userId);
                    
                // recuperer le heros à afficher dans l'entete
                    //tous les heros du $userId
                        $heroes= $this->entityManager->getRepository(Hero::class)-> findBy(['userId'=>$userId]);
                        // dd($heroes);
                    //le dernier hero par son id: 
                        $heroId= end($heroes)->getId();
                        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
                    // le hero possède t'il un backpack
                        $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                        // dd($sac);

                        if($sac ==[]){
                            // création du backpack/inventaire 

                            $backpack= new Backpack();
                            $backpack->setHeroId($hero[0]);
                            $bpId=$backpack->getId();
                                
                            $meal= $this->entityManager->find(Objet::class, 11);
                            $axe= $this->entityManager->find(Objet::class, 19);
                            $backpack->addObjetId($axe);
                            $backpack->addObjetId($meal);

                            $this->entityManager->persist($backpack);
                            $this->entityManager->flush();
                        }
                
                //objets de l'inventaire du héros
                    $items = $this->inventory->inventory($heroId);
                    //dd($items);
                
                //test de présence d'un élément d'armure:
                    $itemsLength=count($items);
                    $end=$hero[0]->getEndMax();
                        for ($i=0; $i<$itemsLength;$i++){
                        
                            $item =   $items[$i];
                            if($item->getId()==23){
                                // ajouter 2 à l'endurance
                                $newEnd=$end+2;
                                $hero[0]->setEndMax($newEnd);
                                $hero[0]->setEndurance($newEnd);
                                $this->entityManager->flush();
                            }
                            if($item->getId()==24){
                                // ajouter 4 à l'endurance
                                $newEnd=$end+4;
                                $hero[0]->setEndMax($newEnd);
                                $hero[0]->setEndurance($newEnd);
                                $this->entityManager->flush();
                            }
                        }

                //stockage des pages visitées    
                    $pagesVues=[];
                    array_push($pagesVues, $p);
                    $hero[0]->setPagesVues($pagesVues);
                    $this->entityManager->flush();

                // Pas de Lunch demander
                    $hero[0]->setLunchClick(0);
                   
                //
            return $this->render('pages/intro.html.twig',[
                'items'=>$items,
                'heroId'=>$heroId, 
                'hero'=>$hero, 
                "p"=>$p
            ]);
        }

// PAGES 1 - 10  

    /**
    * @Route("/page1/{heroId}", name="page_1")
    */
        public function viewPage1(Request $request, $heroId) 
        {    
            $p=1;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page2/{heroId}", name="page_2")
    */
        public function viewPage2(Request $request, $heroId) 
        {    
            $p=2;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page3/{heroId}", name="page_3")
    */
        public function viewPage3(Request $request, $heroId) 
        {   
            $p=3;         

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page4/{heroId}", name="page_4")
    */
        public function viewPage4(Request $request, $heroId) 
        {    
            $p=4;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page5/{heroId}", name="page_5")
    */
        public function viewPage5(Request $request, $heroId) 
        {    
            $p=5;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page6/{heroId}", name="page_6")
    */
        public function viewPage6(Request $request, $heroId) 
        {    
            $p=6;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            return $view;
        }
        
    /**
    * @Route("/page7/{heroId}", name="page_7")
    */
        public function viewPage7(Request $request, $heroId) 
        {    
            $p=7;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            return $view;
        }


    /**
    * @Route("/page8/{heroId}", name="page_8")
    */
        public function viewPage8(Request $request, $heroId) 
        {    
            $p=8;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page9/{heroId}", name="page_9")
    */
        public function viewPage9(Request $request, $heroId) 
        {    
            $p=9;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page10/{heroId}", name="page_10")
    */
        public function viewPage10(Request $request, $heroId) 
        {    
            $p=10;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 11-20
    /**
    * @Route("/page11/{heroId}", name="page_11")
    */
        public function viewPage11(Request $request, $heroId) 
        {    
            $p=11;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page12/{heroId}", name="page_12")
    */
        public function viewPage12(Request $request, $heroId) 
        {    
            $p=12;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page13/{heroId}", name="page_13")
    */
        public function viewPage13(Request $request, $heroId) 
        {    
            $p=13;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page14/{heroId}", name="page_14")
    */
        public function viewPage14(Request $request, $heroId) 
        {    
            $p=14;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page15/{heroId}", name="page_15")
    */
        public function viewPage15(Request $request, $heroId) 
        {    
            $p=15;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page16/{heroId}", name="page_16")
    */
        public function viewPage16(Request $request, $heroId) 
        {    
                $p=16;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page17/{heroId}", name="page_17")
    */
        public function viewPage17(Request $request, $heroId) 
        {    
            $p=17;
        // hors comba    

        $info = $this->getInfoPage($p,$heroId ) ;

        $hero = $info["hero"];
        $items = $info["items"];
        $pagesVues = $info["pagesVues"];
        $isLunch = $info["isLunch"];
        
        //Combat
            //ennemy
            $ennemyId= 1;

        // malus / bonus spécifique au combat
            $circonstance = -1;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

            return $this->render('pages/page17.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page18/{heroId}", name="page_18")
    */
        public function viewPage18(Request $request, $heroId) 
        {    
            $p=18;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }
    
    /**
    * @Route("/page19/{heroId}", name="page_19")
    */
        public function viewPage19(Request $request, $heroId) 
        {    
            $p=19;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page20/{heroId}", name="page_20")
    */
        public function viewPage20(Request $request, $heroId) 
        {    
            $p=20;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    
// PAGES 21 - 30
    /**
    * @Route("/page21/{heroId}", name="page_21")
    */
        public function viewPage21(Request $request, $heroId) 
        {    
            $p=21;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page22/{heroId}", name="page_22")
    */
        public function viewPage22(Request $request, $heroId) 
        {    
            $p=22;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page23/{heroId}", name="page_23")
    */
        public function viewPage23(Request $request, $heroId) 
        {    
            $p=23;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page24/{heroId}", name="page_24")
    */
        public function viewPage24(Request $request, $heroId) 
        {    
            $p=24;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page25/{heroId}", name="page_25")
    */
        public function viewPage25(Request $request, $heroId) 
        {    
            $p=25;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page26/{heroId}", name="page_26")
    */
        public function viewPage26(Request $request, $heroId) 
        {    
            $p=26;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page27/{heroId}", name="page_27")
    */
        public function viewPage27(Request $request, $heroId) 
        {    
            $p=27;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page28/{heroId}", name="page_28")
    */
        public function viewPage28(Request $request, $heroId) 
        {    
            $p=28;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page29/{heroId}", name="page_29")
    */
        public function viewPage29(Request $request, $heroId) 
        {    
            $p=29;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
            
            //Combat
                $ennemyId= 2;

                // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page29.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page30/{heroId}", name="page_30")
    */
        public function viewPage30(Request $request, $heroId) 
        {    
            $p=30;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 31 - 40

    /**
    * @Route("/page31/{heroId}", name="page_31")
    */
        public function viewPage31(Request $request, $heroId) 
        {    
            $p=31;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
        
            return $view;
        }

    /**
    * @Route("/page32/{heroId}", name="page_32")
    */
        public function viewPage32(Request $request, $heroId) 
        {    
            $p=32;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }


    /**
    * @Route("/page33/{heroId}", name="page_33")
    */
        public function viewPage33(Request $request, $heroId) 
        {   
            $p=33;         

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
           
            return $view;
        }

    /**
    * @Route("/page34/{heroId}", name="page_34")
    */
        public function viewPage34(Request $request, $heroId) 
        {    
            $p=34;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
               
            //Combat
                //ennemy
                    $ennemyId= 2;
                
                // malus / bonus spécifique au combat
                    $circonstance = 0;
                  
                
                    $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);
                    // dd($baston);
                    $CR = $baston["CR"];
                    $CS = $baston["CS"];
                    $fightId = $baston["fightId"];
                    $ennemy = $baston["ennemy"];
                    
                    // dd($ennemy[0], $CS, $CR, $fightId);
            return $this->render('pages/page34.html.twig',[
                    "ennemy" => $ennemy ,
                    "ennemyId" => $ennemyId ,
                    "items" => $items , 
                    "hero" => $hero , 
                    "heroId" => $heroId ,
                    "p" => $p , 
                    "isLunch" => $isLunch ,                    
                    "CS" => $CS ,
                    "CR" => $CR ,
                    "fightId" => $fightId ,
                                        
            ]);
          
        }

    /**
    * @Route("/page35/{heroId}", name="page_35")
    */
        public function viewPage35(Request $request, $heroId) 
        {    
            $p=35;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page36/{heroId}", name="page_36")
    */
        public function viewPage36(Request $request, $heroId) 
        {    
            $p=36;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page37/{heroId}", name="page_37")
    */
        public function viewPage37(Request $request, $heroId) 
        {    
            $p=37;      

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page38/{heroId}", name="page_38")
    */
        public function viewPage38(Request $request, $heroId) 
        {    
            $p=38;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];           

            return $view;
        }

    /**
    * @Route("/page39/{heroId}", name="page_39")
    */
        public function viewPage39(Request $request, $heroId) 
        {    
            $p=39;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page40/{heroId}", name="page_40")
    */
        public function viewPage40(Request $request, $heroId) 
        {    
            $p=40;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    

// PAGES 41 - 50

    /**
    * @Route("/page41/{heroId}", name="page_41")
    */
        public function viewPage41(Request $request, $heroId) 
        {    
            $p=41;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page42/{heroId}", name="page_42")
    */
        public function viewPage42(Request $request, $heroId) 
        {    
            $p=42;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page43/{heroId}", name="page_43")
    */
        public function viewPage43(Request $request, $heroId) 
        {    
            $p=43;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
            
            //Combat
                //ennemy
                $ennemyId= 3;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


                return $this->render('pages/page43.html.twig',[
                    'items'=>$items, 
                    'hero'=>$hero, 
                    'heroId'=>$heroId, 
                    "p"=>$p, 
                    'isLunch'=>$isLunch,
                    'ennemy'=>$ennemy,
                    "CS"=>$CS,
                    "CR"=>$CR,
                    'fightId'=>$fightId,
                ]);
            }

    /**
    * @Route("/page44/{heroId}", name="page_44")
    */
        public function viewPage44(Request $request, $heroId) 
        {    
            $p=44;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page45/{heroId}", name="page_45")
    */
        public function viewPage45(Request $request, $heroId) 
        {    
            $p=45;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page46/{heroId}", name="page_46")
    */
        public function viewPage46(Request $request, $heroId) 
        {    
            $p=46;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];   

            return $view;
        }

    /**
    * @Route("/page47/{heroId}", name="page_47")
    */
        public function viewPage47(Request $request, $heroId) 
        {    
            $p=47;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page48/{heroId}", name="page_48")
    */
        public function viewPage48(Request $request, $heroId) 
        {    
            $p=48;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }

    /**
    * @Route("/page49/{heroId}", name="page_49")
    */
        public function viewPage49(Request $request, $heroId) 
        {    
            $p=49;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page50/{heroId}", name="page_50")
    */
        public function viewPage50(Request $request, $heroId) 
        {    
            $p=50;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 51 - 60

    /**
    * @Route("/page51/{heroId}", name="page_51")
    */
        public function viewPage51(Request $request, $heroId) 
        {    
            $p=51;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page52/{heroId}", name="page_52")
    */
        public function viewPage52(Request $request, $heroId) 
        {    
            $p=52;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page53/{heroId}", name="page_53")
    */
        public function viewPage53(Request $request, $heroId) 
        {    
            $p=53;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];         

            return $view;
        }

    /**
    * @Route("/page54/{heroId}", name="page_54")
    */
        public function viewPage54(Request $request, $heroId) 
        {    
            $p=54;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }

    /**
    * @Route("/page55/{heroId}", name="page_55")
    */
        public function viewPage55(Request $request, $heroId) 
        {    
            $p=55;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
            //Combat
                //ennemy
                $ennemyId= 4;

            // malus / bonus spécifique au combat
                $circonstance = 4;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


                return $this->render('pages/page55.html.twig',[
                    'items'=>$items, 
                    'hero'=>$hero, 
                    'heroId'=>$heroId, 
                    "p"=>$p, 
                    'isLunch'=>$isLunch,
                    'ennemy'=>$ennemy,
                    "CS"=>$CS,
                    "CR"=>$CR,
                    'fightId'=>$fightId,
                ]);
            }

    /**
    * @Route("/page56/{heroId}", name="page_56")
    */
        public function viewPage56(Request $request, $heroId) 
        {   
            $p=56;         

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page57/{heroId}", name="page_57")
    */
        public function viewPage57(Request $request, $heroId) 
        {    
            $p=57;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page58/{heroId}", name="page_58")
    */
        public function viewPage58(Request $request, $heroId) 
        {    
            $p=58;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page59/{heroId}", name="page_59")
    */
        public function viewPage59(Request $request, $heroId) 
        {    
            $p=59;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page60/{heroId}", name="page_60")
    */
        public function viewPage60(Request $request, $heroId) 
        {    
            $p=60;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }

// PAGES 61 - 70

    /**
    * @Route("/page61/{heroId}", name="page_61")
    */
        public function viewPage61(Request $request, $heroId) 
        {    
            $p=61;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page62/{heroId}", name="page_62")
    */
        public function viewPage62(Request $request, $heroId) 
        {    
            $p=62;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page63/{heroId}", name="page_63")
    */
        public function viewPage63(Request $request, $heroId) 
        {    
            $p=63;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 7;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


            return $this->render('pages/page63.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page64/{heroId}", name="page_64")
    */
        public function viewPage64(Request $request, $heroId) 
        {    
            $p=64;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page65/{heroId}", name="page_65")
    */
        public function viewPage65(Request $request, $heroId) 
        {    
            $p=65;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page66/{heroId}", name="page_66")
    */
        public function viewPage66(Request $request, $heroId) 
        {    
            $p=66;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page67/{heroId}", name="page_67")
    */
        public function viewPage67(Request $request, $heroId) 
        {    
            $p=67;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page68/{heroId}", name="page_68")
    */
        public function viewPage68(Request $request, $heroId) 
        {    
            $p=68;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page69/{heroId}", name="page_69")
    */
        public function viewPage69(Request $request, $heroId) 
        {    
            $p=69;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page70/{heroId}", name="page_70")
    */
        public function viewPage70(Request $request, $heroId) 
        {    
            $p=70;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 71 - 80
    /**
    * @Route("/page71/{heroId}", name="page_71")
    */
        public function viewPage71(Request $request, $heroId) 
        {    
            $p=71;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page72/{heroId}", name="page_72")
    */
        public function viewPage72(Request $request, $heroId) 
        {    
            $p=72;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 8;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


            return $this->render('pages/page72.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page73/{heroId}", name="page_73")
    */
        public function viewPage73(Request $request, $heroId) 
        {    
            $p=73;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }
    
    /**
    * @Route("/page74/{heroId}", name="page_74")
    */
        public function viewPage74(Request $request, $heroId) 
        {    
            $p=74;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page75/{heroId}", name="page_75")
    */
        public function viewPage75(Request $request, $heroId) 
        {    
            $p=75;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page76/{heroId}", name="page_76")
    */
        public function viewPage76(Request $request, $heroId) 
        {    
            $p=76;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page77/{heroId}", name="page_77")
    */
        public function viewPage77(Request $request, $heroId) 
        {    
            $p=77;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page78/{heroId}", name="page_78")
    */
        public function viewPage78(Request $request, $heroId) 
        {    
            $p=78;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page79/{heroId}", name="page_79")
    */
        public function viewPage79(Request $request, $heroId) 
        {    
            $p=79;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page80/{heroId}", name="page_80")
    */
        public function viewPage80(Request $request, $heroId) 
        {    
            $p=80;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 81 - 90

    /**
    * @Route("/page81/{heroId}", name="page_81")
    */
        public function viewPage81(Request $request, $heroId) 
        {    
            $p=81;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page82/{heroId}", name="page_82")
    */
        public function viewPage82(Request $request, $heroId) 
        {    
            $p=82;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page83/{heroId}", name="page_83")
    */
        public function viewPage83(Request $request, $heroId) 
        {    
            $p=83;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page84/{heroId}", name="page_84")
    */
        public function viewPage84(Request $request, $heroId) 
        {    
            $p=84;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];       

            return $view;
        }

    /**
    * @Route("/page85/{heroId}", name="page_85")
    */
        public function viewPage85(Request $request, $heroId) 
        {    
            $p=85;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page86/{heroId}", name="page_86")
    */
        public function viewPage86(Request $request, $heroId) 
        {    
            $p=86;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page87/{heroId}", name="page_87")
    */
        public function viewPage87(Request $request, $heroId) 
        {    
            $p=87;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page88/{heroId}", name="page_88")
    */
        public function viewPage88(Request $request, $heroId) 
        {    
            $p=88;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page89/{heroId}", name="page_89")
    */
        public function viewPage89(Request $request, $heroId) 
        {    
            $p=89;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }

    /**
    * @Route("/page90/{heroId}", name="page_90")
    */
        public function viewPage90(Request $request, $heroId) 
        {    
            $p=90;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 91 - 100
    
    /**
    * @Route("/page91/{heroId}", name="page_91")
    */
        public function viewPage91(Request $request, $heroId) 
        {    
            $p=91;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page92/{heroId}", name="page_92")
    */
        public function viewPage92(Request $request, $heroId) 
        {    
            $p=92;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page93/{heroId}", name="page_93")
    */
        public function viewPage93(Request $request, $heroId) 
        {    
            $p=93;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page94/{heroId}", name="page_94")
    */
        public function viewPage94(Request $request, $heroId) 
        {    
            $p=94;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page95/{heroId}", name="page_95")
    */
        public function viewPage95(Request $request, $heroId) 
        {    
            $p=95;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page96/{heroId}", name="page_96")
    */
        public function viewPage96(Request $request, $heroId) 
        {    
            $p=96;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page97/{heroId}", name="page_97")
    */
        public function viewPage97(Request $request, $heroId) 
        {    
            $p=97;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page98/{heroId}", name="page_98")
    */
        public function viewPage98(Request $request, $heroId) 
        {    
            $p=98;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page99/{heroId}", name="page_99")
    */
        public function viewPage99(Request $request, $heroId) 
        {    
            $p=99;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];       

            return $view;
        }

    /**
    * @Route("/page100/{heroId}", name="page_100")
    */
        public function viewPage100(Request $request, $heroId) 
        {    
            $p=100;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 101 - 110

    /**
    * @Route("/page101/{heroId}", name="page_101")
    */
        public function viewPage101(Request $request, $heroId) 
        {    
            $p=101;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page102/{heroId}", name="page_102")
    */
        public function viewPage102(Request $request, $heroId) 
        {    
            $p=102;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page103/{heroId}", name="page_103")
    */
        public function viewPage103(Request $request, $heroId) 
        {    
            $p=103;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page104/{heroId}", name="page_104")
    */
        public function viewPage104(Request $request, $heroId) 
        {    
            $p=104;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page105/{heroId}", name="page_105")
    */
        public function viewPage105(Request $request, $heroId) 
        {    
            $p=105;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page106/{heroId}", name="page_106")
    */
        public function viewPage106(Request $request, $heroId) 
        {    
            $p=106;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page107/{heroId}", name="page_107")
    */
        public function viewPage107(Request $request, $heroId) 
        {    
            $p=107;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];       

            return $view;
        }

    /**
    * @Route("/page108/{heroId}", name="page_108")
    */
        public function viewPage108(Request $request, $heroId) 
        {    
            $p=108;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page109/{heroId}", name="page_109")
    */
        public function viewPage109(Request $request, $heroId) 
        {    
            $p=109;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page110/{heroId}", name="page_110")
    */
        public function viewPage110(Request $request, $heroId) 
        {    
            $p=110;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 111 - 120

    /**
    * @Route("/page111/{heroId}", name="page_111")
    */
        public function viewPage111(Request $request, $heroId) 
        {    
            $p=111;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page112/{heroId}", name="page_112")
    */
        public function viewPage112(Request $request, $heroId) 
        {    
            $p=112;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 5;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


            return $this->render('pages/page112.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page1122/{heroId}", name="page_1122")
    */
        public function viewPage1122(Request $request, $heroId) 
        {    
            $p=1122;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 6;

        // malus / bonus spécifique au combat
            $circonstance = 0;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];


            return $this->render('pages/page1122.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page113/{heroId}", name="page_113")
    */
        public function viewPage113(Request $request, $heroId) 
        {    
            $p=113;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page114/{heroId}", name="page_114")
    */
        public function viewPage114(Request $request, $heroId) 
        {    
            $p=114;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page115/{heroId}", name="page_115")
    */
        public function viewPage115(Request $request, $heroId) 
        {    
            $p=115;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page116/{heroId}", name="page_116")
    */
        public function viewPage116(Request $request, $heroId) 
        {    
            $p=116;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page117/{heroId}", name="page_117")
    */
        public function viewPage117(Request $request, $heroId) 
        {    
            $p=117;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page118/{heroId}", name="page_118")
    */
        public function viewPage118(Request $request, $heroId) 
        {    
            $p=118;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page119/{heroId}", name="page_119")
    */
        public function viewPage119(Request $request, $heroId) 
        {    
            $p=119;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];       

            return $view;
        }


    /**
    * @Route("/page120/{heroId}", name="page_120")
    */
        public function viewPage120(Request $request, $heroId) 
        {    
            $p=120;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }
// PAGES 121 - 130

    /**
    * @Route("/page121/{heroId}", name="page_121")
    */
        public function viewPage121(Request $request, $heroId) 
        {    
            $p=121;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page122/{heroId}", name="page_122")
    */
        public function viewPage122(Request $request, $heroId) 
        {    
            $p=122;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page123/{heroId}", name="page_123")
    */
        public function viewPage123(Request $request, $heroId) 
        {    
            $p=123;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];     

            return $view;
        }

    /**
    * @Route("/page124/{heroId}", name="page_124")
    */
        public function viewPage124(Request $request, $heroId) 
        {    
            $p=124;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];         

            return $view;
        }

    /**
    * @Route("/page125/{heroId}", name="page_125")
    */
        public function viewPage125(Request $request, $heroId) 
        {    
            $p=125;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page126/{heroId}", name="page_126")
    */
        public function viewPage126(Request $request, $heroId) 
        {    
            $p=126;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page127/{heroId}", name="page_127")
    */
        public function viewPage127(Request $request, $heroId) 
        {    
            $p= 127;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
           
            return $view;
        }

    /**
    * @Route("/page128/{heroId}", name="page_128")
    */
        public function viewPage128(Request $request, $heroId) 
        {    
            $p=128;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page129/{heroId}", name="page_129")
    */
        public function viewPage129(Request $request, $heroId) 
        {    
            $p=129;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page130/{heroId}", name="page_130")
    */
        public function viewPage130(Request $request, $heroId) 
        {    
            $p=130;      

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 131 - 140

    /**
    * @Route("/page131/{heroId}", name="page_131")
    */
        public function viewPage131(Request $request, $heroId) 
        {   
            $p=131;         

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page132/{heroId}", name="page_132")
    */
        public function viewPage132(Request $request, $heroId) 
        {    
            $p=132;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page133/{heroId}", name="page_133")
    */
        public function viewPage133(Request $request, $heroId) 
        {    
            $p=133;
            // hors combat
            

                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 10;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];


            return $this->render('pages/page133.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page134/{heroId}", name="page_134")
    */
        public function viewPage134(Request $request, $heroId) 
        {    
            $p=134;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page135/{heroId}", name="page_135")
    */
        public function viewPage135(Request $request, $heroId) 
        {    
            $p=135;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page136/{heroId}", name="page_136")
    */
        public function viewPage136(Request $request, $heroId) 
        {    
            $p=136;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 5;

        // malus / bonus spécifique au combat
            $circonstance = 1;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

            return $this->render('pages/page136.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page1362/{heroId}", name="page_1362")
    */
        public function viewPage1362(Request $request, $heroId) 
        {    
            $p=1362;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 6;

        // malus / bonus spécifique au combat
            $circonstance = 1;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

            return $this->render('pages/page1362.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page137/{heroId}", name="page_137")
    */
        public function viewPage137(Request $request, $heroId) 
        {    
            $p=137;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page138/{heroId}", name="page_138")
    */
        public function viewPage138(Request $request, $heroId) 
        {    
            $p=138;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 5;

        // malus / bonus spécifique au combat
            $circonstance = 0;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

            return $this->render('pages/page138.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }        

    /**
    * @Route("/page1382/{heroId}", name="page_1382")
    */
        public function viewPage1382(Request $request, $heroId) 
        {    
            $p=1382;
            // hors combat
            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            //
        //Combat
            //ennemy
            $ennemyId= 6;

        // malus / bonus spécifique au combat
            $circonstance = 0;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

            return $this->render('pages/page1382.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page139/{heroId}", name="page_139")
    */
        public function viewPage139(Request $request, $heroId) 
        {    
            $p=139;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];           

            return $view;
        }

    /**
    * @Route("/page140/{heroId}", name="page_140")
    */
        public function viewPage140(Request $request, $heroId) 
        {    
            $p=140;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

// PAGES 141 - 150

    /**
    * @Route("/page141/{heroId}", name="page_141")
    */
        public function viewPage141(Request $request, $heroId) 
        {     
            $p=141;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page142/{heroId}", name="page_142")
    */
        public function viewPage142(Request $request, $heroId) 
        {    
            $p=142;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page143/{heroId}", name="page_143")
    */
        public function viewPage143(Request $request, $heroId) 
        {    
            $p=143;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page144/{heroId}", name="page_144")
    */
        public function viewPage144(Request $request, $heroId) 
        {    
            $p=144;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            $hero = $info['hero'];
            $items = $info['items'];

            //vol dans la foule
                $isBackpack = $hero[0]->getBackpack();

                $weaponChoice = array();
                $bpChoice = array();
                 
                $count= count($items);
                                                 
                $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);

                for($i=0;$i<$count; $i++){
                    $type = $items[$i]->getObjType();                                                
                    $idItem = $items[$i]->getId();
                    $place = $items[$i]->getPlace();
                    
                    if($type == "weapon" ){
                        $weaponChoice[] = $idItem;                                
                    }
                    if($place == "backpack" ){
                        $bpChoice[] = $idItem;                                
                    }
                }
                
                if ($isBackpack==0 || $bpChoice==[] ){
                    $itemLoose= rand(0,count($weaponChoice)-1);
                    $idItemLoose= $weaponChoice[$itemLoose];
                }else{
                    $itemLoose= rand(0,count($bpChoice)-1);
                    $idItemLoose= $bpChoice[$itemLoose];
                }
                $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($idItemLoose);
                $sac[0]->removeObjetId($objectLoose);
                
                $this->entityManager->flush($sac[0]);
            
            // perte endurance bousculade
                $end= $hero[0]->getEndurance();
                $newEnd = $end - 2;
                $hero[0] ->setEndurance($newEnd);
                $this->entityManager->persist($hero[0]);
                $this->entityManager->flush($hero[0]);


            return $view;
        }

    /**
    * @Route("/page145/{heroId}", name="page_145")
    */
        public function viewPage145(Request $request, $heroId) 
        {    
            $p=145;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page146/{heroId}", name="page_146")
    */
        public function viewPage146(Request $request, $heroId) 
        {    
            $p=146;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page147/{heroId}", name="page_147")
    */
        public function viewPage147(Request $request, $heroId) 
        {    
            $p=147;      

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];            

            return $view;
        }

    /**
    * @Route("/page148/{heroId}", name="page_148")
    */
        public function viewPage148(Request $request, $heroId) 
        {    
            $p=148;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }


    /**
    * @Route("/page149/{heroId}", name="page_149")
    */
        public function viewPage149(Request $request, $heroId) 
        {    
            $p=149;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
    
            return $view;
        }

    /**
    * @Route("/page150/{heroId}", name="page_150")
    */
        public function viewPage150(Request $request, $heroId) 
        {    
            $p=150;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];           

            return $view;
        }

// PAGES 151 - 160
    /**
    * @Route("/page151/{heroId}", name="page_151")
    */
        public function viewPage151(Request $request, $heroId) 
        {    
            $p=151;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page152/{heroId}", name="page_152")
    */
        public function viewPage152(Request $request, $heroId) 
        {    
            $p=152;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page153/{heroId}", name="page_153")
    */
        public function viewPage153(Request $request, $heroId) 
        {    
            $p=153;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page154/{heroId}", name="page_154")
    */
        public function viewPage154(Request $request, $heroId) 
        {    
            $p=154;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                        
            return $view;
        }

    /**
    * @Route("/page155/{heroId}", name="page_155")
    */
        public function viewPage155(Request $request, $heroId) 
        {    
            $p=155;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page156/{heroId}", name="page_156")
    */
        public function viewPage156(Request $request, $heroId) 
        {    
            $p=156;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page157/{heroId}", name="page_157")
    */
        public function viewPage157(Request $request, $heroId) 
        {    
            $p=157;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page158/{heroId}", name="page_158")
    */
        public function viewPage158(Request $request, $heroId) 
        {    
            $p=158;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }
   
    /**
    * @Route("/page159/{heroId}", name="page_159")
    */
        public function viewPage159(Request $request, $heroId) 
        {    
            $p=159;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page160/{heroId}", name="page_160")
    */
        public function viewPage160(Request $request, $heroId) 
        {    
            $p=160;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

// PAGES 161 - 170    

    /**
    * @Route("/page161/{heroId}", name="page_161")
    */
        public function viewPage161(Request $request, $heroId) 
        {    
            $p=161;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page162/{heroId}", name="page_162")
    */
        public function viewPage162(Request $request, $heroId) 
        {    
            $p=162;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];           

            return $view;
        }

    /**
    * @Route("/page163/{heroId}", name="page_163")
    */
        public function viewPage163(Request $request, $heroId) 
        {    
            $p=163;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page164/{heroId}", name="page_164")
    */
        public function viewPage164(Request $request, $heroId) 
        {    
            $p=164;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page165/{heroId}", name="page_165")
    */
        public function viewPage165(Request $request, $heroId) 
        {    
            $p=165;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page166/{heroId}", name="page_166")
    */
        public function viewPage166(Request $request, $heroId) 
        {    
            $p=166;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page167/{heroId}", name="page_167")
    */
        public function viewPage167(Request $request, $heroId) 
        {    
            $p=167;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page168/{heroId}", name="page_168")
    */
        public function viewPage168(Request $request, $heroId) 
        {    
            $p=168;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page169/{heroId}", name="page_169")
    */
        public function viewPage169(Request $request, $heroId) 
        {    
            $p=169;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 11;

                // malus / bonus spécifique au combat
                    $circonstance = 0;

                    $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                    $CR = $baston["CR"];
                    $CS = $baston["CS"];
                    $fightId = $baston["fightId"];
                    $ennemy = $baston["ennemy"];

            return $this->render('pages/page169.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page170/{heroId}", name="page_170")
    */
        public function viewPage170(Request $request, $heroId) 
        {    
            $p=170;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 12;

                // malus / bonus spécifique au combat
                    $circonstance = 0;

                    $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                    $CR = $baston["CR"];
                    $CS = $baston["CS"];
                    $fightId = $baston["fightId"];
                    $ennemy = $baston["ennemy"];

            return $this->render('pages/page170.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

// PAGES 171 - 180

    /**
    * @Route("/page171/{heroId}", name="page_171")
    */
        public function viewPage171(Request $request, $heroId) 
        {    
            $p=171;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page172/{heroId}", name="page_172")
    */
        public function viewPage172(Request $request, $heroId) 
        {    
            $p=172;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page173/{heroId}", name="page_173")
    */
        public function viewPage173(Request $request, $heroId) 
        {    
            $p=173;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page174/{heroId}", name="page_174")
    */
        public function viewPage174(Request $request, $heroId) 
        {    
            $p=174;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page175/{heroId}", name="page_175")
    */
        public function viewPage175(Request $request, $heroId) 
        {    
            $p=175;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page176/{heroId}", name="page_176")
    */
        public function viewPage176(Request $request, $heroId) 
        {    
            $p=176;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page177/{heroId}", name="page_177")
    */
        public function viewPage177(Request $request, $heroId) 
        {    
            $p=177;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page178/{heroId}", name="page_178")
    */
        public function viewPage178(Request $request, $heroId) 
        {    
            $p=178;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page179/{heroId}", name="page_179")
    */
        public function viewPage179(Request $request, $heroId) 
        {    
            $p=179;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page180/{heroId}", name="page_180")
    */
        public function viewPage180(Request $request, $heroId) 
        {    
            $p=180;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 13;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page180.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page1802/{heroId}", name="page_1802")
    */
        public function viewPage1802(Request $request, $heroId) 
        {    
            $p=1802;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 14;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page1802.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page1803/{heroId}", name="page_1803")
    */
        public function viewPage1803(Request $request, $heroId) 
        {    
            $p=1803;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 15;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page1803.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

// PAGES 181 - 190

    /**
    * @Route("/page181/{heroId}", name="page_181")
    */
        public function viewPage181(Request $request, $heroId) 
        {    
            $p=181;
            $info = $this->getInfoPage($p,$heroId ) ;
            // $view = $info["view"];
            $hero = $info["hero"];
            $items = $info["items"];
            $pagesVues = $info["pagesVues"];
            $isLunch = $info["isLunch"];

            // d'où venez vous messire?
            $page205=false;
                if (in_array(205, $pagesVues,true)==true){
                    $page205=true;
                }
            
            return $this->render('pages/page181.html.twig',['items'=>$items, 'hero'=>$hero, 'heroId'=>$heroId, "p"=>$p, 'isLunch'=>$isLunch, 'p205'=>$page205]);
        }

    /**
    * @Route("/page182/{heroId}", name="page_182")
    */
        public function viewPage182(Request $request, $heroId) 
        {    
            $p=182;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page183/{heroId}", name="page_183")
    */
        public function viewPage183(Request $request, $heroId) 
        {    
            $p=183;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page184/{heroId}", name="page_184")
    */
        public function viewPage184(Request $request, $heroId) 
        {    
            $p=184;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page185/{heroId}", name="page_185")
    */
        public function viewPage185(Request $request, $heroId) 
        {    
            $p=185;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page186/{heroId}", name="page_186")
    */
        public function viewPage186(Request $request, $heroId) 
        {    
            $p=186;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page187/{heroId}", name="page_187")
    */
        public function viewPage187(Request $request, $heroId) 
        {    
            $p=187;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page188/{heroId}", name="page_188")
    */
        public function viewPage188(Request $request, $heroId) 
        {    
            $p=188;
            $info = $this->getInfoPage($p,$heroId ) ;
            $hero = $info["hero"];
            $items = $info["items"];
            $pagesVues = $info["pagesVues"];
            $isLunch = $info["isLunch"];

            // perte du sac ou perte d'endurance
            
                $rand=rand(0,9);
                $end=$hero[0]->getEndurance();
                
                $sac= $this->entityManager->getRepository(backpack::class)-> findBy(['heroId'=>$heroId]);
                $count= count($items);
                
                if($rand<7){                        
                    $hero[0]->setBackpack(0); 
                    $this->entityManager->flush($hero[0]); 
                    
                    for($i=0;$i<$count; $i++){
                        $idItem = $items[$i]->getId();
                        $place = $items[$i]->getPlace();
                        
                        if( $place == "backpack" ){
                            $objectLoose=$this->entityManager->getRepository(Objet::class)-> findOneById($idItem);
                            $sac[0]->removeObjetId($objectLoose); 
                            $this->entityManager->flush($sac[0]);                                
                        }
                    }
                }else{
                    $hero[0]->setEndurance($end-3);
                    $this->entityManager->flush($hero[0]);
                }
                       
            return $this->render('pages/page188.html.twig',['items'=>$items, 'hero'=>$hero, 'heroId'=>$heroId, "p"=>$p, 'isLunch'=>$isLunch,'rand'=>$rand]);
        }

    /**
    * @Route("/page189/{heroId}", name="page_189")
    */
        public function viewPage189(Request $request, $heroId) 
        {    
            $p=189;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page190/{heroId}", name="page_190")
    */
        public function viewPage190(Request $request, $heroId) 
        {    
            $p=190;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

// PAGES 191 - 200

    /**
    * @Route("/page191/{heroId}", name="page_191")
    */
        public function viewPage191(Request $request, $heroId) 
        {    
            $p=191;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 16;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page191.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page192/{heroId}", name="page_192")
    */
        public function viewPage192(Request $request, $heroId) 
        {    
            $p=192;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }
    /**
    * @Route("/page193/{heroId}", name="page_193")
    */
        public function viewPage193(Request $request, $heroId) 
        {    
            $p=193;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page194/{heroId}", name="page_194")
    */
        public function viewPage194(Request $request, $heroId) 
        {    
            $p=194;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page195/{heroId}", name="page_195")
    */
        public function viewPage195(Request $request, $heroId) 
        {    
            $p=195;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page196/{heroId}", name="page_196")
    */
        public function viewPage196(Request $request, $heroId) 
        {    
            $p=196;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page197/{heroId}", name="page_197")
    */
        public function viewPage197(Request $request, $heroId) 
        {    
            $p=197;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page198/{heroId}", name="page_198")
    */
        public function viewPage198(Request $request, $heroId) 
        {    
            $p=198;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page199/{heroId}", name="page_199")
    */
        public function viewPage199(Request $request, $heroId) 
        {    
            $p=199;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page200/{heroId}", name="page_200")
    */
        public function viewPage200(Request $request, $heroId) 
        {    
            $p=200;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

// PAGES 201 - 210

    /**
    * @Route("/page201/{heroId}", name="page_201")
    */
        public function viewPage201(Request $request, $heroId) 
        {    
            $p=201;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page202/{heroId}", name="page_202")
    */
        public function viewPage202(Request $request, $heroId) 
        {    
            $p=202;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page203/{heroId}", name="page_203")
    */
        public function viewPage203(Request $request, $heroId) 
        {    
            $p=203;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page204/{heroId}", name="page_204")
    */
        public function viewPage204(Request $request, $heroId) 
        {    
            $p=204;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page205/{heroId}", name="page_205")
    */
        public function viewPage205(Request $request, $heroId) 
        {    
            $p=205;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            
            return $view;
        }

    /**
    * @Route("/page206/{heroId}", name="page_206")
    */
        public function viewPage206(Request $request, $heroId) 
        {    
            $p=206;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page207/{heroId}", name="page_207")
    */
        public function viewPage207(Request $request, $heroId) 
        {    
            $p=207;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page208/{heroId}", name="page_208")
    */
        public function viewPage208(Request $request, $heroId) 
        {    
            $p=208;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 18;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page208.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page209/{heroId}", name="page_209")
    */
        public function viewPage209(Request $request, $heroId) 
        {    
            $p=209;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page210/{heroId}", name="page_210")
    */
        public function viewPage210(Request $request, $heroId) 
        {    
            $p=210;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 211 - 220
    /**
    * @Route("/page211/{heroId}", name="page_211")
    */
        public function viewPage211(Request $request, $heroId) 
        {    
            $p=211;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page212/{heroId}", name="page_212")
    */
        public function viewPage212(Request $request, $heroId) 
        {    
            $p=212;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page213/{heroId}", name="page_213")
    */
        public function viewPage213(Request $request, $heroId) 
        {    
            $p=213;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page214/{heroId}", name="page_214")
    */
        public function viewPage214(Request $request, $heroId) 
        {    
            $p=214;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page215/{heroId}", name="page_215")
    */
        public function viewPage215(Request $request, $heroId) 
        {    
            $p=215;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page216/{heroId}", name="page_216")
    */
        public function viewPage216(Request $request, $heroId) 
        {    
            $p=216;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

        /**
    * @Route("/page217/{heroId}", name="page_217")
    */
        public function viewPage217(Request $request, $heroId) 
        {    
            $p=217;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page218/{heroId}", name="page_218")
    */
        public function viewPage218(Request $request, $heroId) 
        {    
            $p=218;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page219/{heroId}", name="page_219")
    */
        public function viewPage219(Request $request, $heroId) 
        {    
            $p=219;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page220/{heroId}", name="page_220")
    */
        public function viewPage220(Request $request, $heroId) 
        {    
            $p=220;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 17;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page220.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

// PAGES 221 - 230

    /**
    * @Route("/page221/{heroId}", name="page_221")
    */
        public function viewPage221(Request $request, $heroId) 
        {    
            $p=221;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page222/{heroId}", name="page_222")
    */
        public function viewPage222(Request $request, $heroId) 
        {    
            $p=222;   

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page223/{heroId}", name="page_223")
    */
        public function viewPage223(Request $request, $heroId) 
        {    
            $p=223;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }
        
    /**
    * @Route("/page224/{heroId}", name="page_224")
    */
        public function viewPage224(Request $request, $heroId) 
        {    
            $p=224;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page225/{heroId}", name="page_225")
    */
        public function viewPage225(Request $request, $heroId) 
        {    
            $p=225;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page226/{heroId}", name="page_226")
    */
        public function viewPage226(Request $request, $heroId) 
        {    
            $p=226;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page227/{heroId}", name="page_227")
    */
        public function viewPage227(Request $request, $heroId) 
        {    
            $p=227;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 19;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page227.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page228/{heroId}", name="page_228")
    */
        public function viewPage228(Request $request, $heroId) 
        {    
            $p=228;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page229/{heroId}", name="page_229")
    */
        public function viewPage229(Request $request, $heroId) 
        {    
            $p=229;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 20;

            // malus / bonus spécifique au combat
                $circonstance = -1;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page229.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page230/{heroId}", name="page_230")
    */
        public function viewPage230(Request $request, $heroId) 
        {    
            $p=230;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 231 - 240

    /**
    * @Route("/page231/{heroId}", name="page_231")
    */
        public function viewPage231(Request $request, $heroId) 
        {    
            $p=231;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 21;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page231.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page232/{heroId}", name="page_232")
    */
        public function viewPage232(Request $request, $heroId) 
        {    
            $p=232;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page233/{heroId}", name="page_233")
    */
        public function viewPage233(Request $request, $heroId) 
        {    
            $p=233;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page234/{heroId}", name="page_234")
    */
        public function viewPage234(Request $request, $heroId) 
        {    
            $p=234;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page235/{heroId}", name="page_235")
    */
        public function viewPage235(Request $request, $heroId) 
        {    
            $p=235;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page236/{heroId}", name="page_236")
    */
        public function viewPage236(Request $request, $heroId) 
        {    
            $p=236;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page237/{heroId}", name="page_237")
    */
        public function viewPage237(Request $request, $heroId) 
        {    
            $p=237;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page238/{heroId}", name="page_238")
    */
        public function viewPage238(Request $request, $heroId) 
        {    
            $p=238;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page239/{heroId}", name="page_239")
    */
        public function viewPage239(Request $request, $heroId) 
        {    
            $p=239;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page240/{heroId}", name="page_240")
    */
        public function viewPage240(Request $request, $heroId) 
        {    
            $p=240;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 241 - 250

    /**
    * @Route("/page241/{heroId}", name="page_241")
    */
        public function viewPage241(Request $request, $heroId) 
        {    
            $p=241;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page242/{heroId}", name="page_242")
    */
        public function viewPage242(Request $request, $heroId) 
        {    
            $p=242;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page243/{heroId}", name="page_243")
    */
        public function viewPage243(Request $request, $heroId) 
        {    
            $p=243;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page244/{heroId}", name="page_244")
    */
        public function viewPage244(Request $request, $heroId) 
        {    
            $p=244;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page245/{heroId}", name="page_245")
    */
        public function viewPage245(Request $request, $heroId) 
        {    
            $p=245;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page246/{heroId}", name="page_246")
    */
        public function viewPage246(Request $request, $heroId) 
        {    
            $p=246;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 22;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page246.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page247/{heroId}", name="page_247")
    */
        public function viewPage247(Request $request, $heroId) 
        {    
            $p=247;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page248/{heroId}", name="page_248")
    */
        public function viewPage248(Request $request, $heroId) 
        {    
            $p=248;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page249/{heroId}", name="page_249")
    */
        public function viewPage249(Request $request, $heroId) 
        {    
            $p=249;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page250/{heroId}", name="page_250")
    */
        public function viewPage250(Request $request, $heroId) 
        {    
            $p=250;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 251 - 260

    /**
    * @Route("/page251/{heroId}", name="page_251")
    */
        public function viewPage251(Request $request, $heroId) 
        {    
            $p=251;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page252/{heroId}", name="page_252")
    */
        public function viewPage252(Request $request, $heroId) 
        {    
            $p=252;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page253/{heroId}", name="page_253")
    */
        public function viewPage253(Request $request, $heroId) 
        {    
            $p=253;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 23;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page253.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page2532/{heroId}", name="page_2532")
    */
        public function viewPage2532(Request $request, $heroId) 
        {    
            $p=2532;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 24;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page2532.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page2533/{heroId}", name="page_2533")
    */
        public function viewPage2533(Request $request, $heroId) 
        {    
            $p=2533;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 25;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page2533.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page2534/{heroId}", name="page_2534")
    */
        public function viewPage2534(Request $request, $heroId) 
        {    
            $p=2534;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 26;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page2534.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page254/{heroId}", name="page_254")
    */
        public function viewPage254(Request $request, $heroId) 
        {    
            $p=254;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page255/{heroId}", name="page_255")
    */
        public function viewPage255(Request $request, $heroId) 
        {    
            $p=255;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 32;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page255.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page256/{heroId}", name="page_256")
    */
        public function viewPage256(Request $request, $heroId) 
        {    
            $p=256;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page257/{heroId}", name="page_257")
    */
        public function viewPage257(Request $request, $heroId) 
        {    
            $p=257;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page258/{heroId}", name="page_258")
    */
        public function viewPage258(Request $request, $heroId) 
        {    
            $p=258;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page259/{heroId}", name="page_259")
    */
        public function viewPage259(Request $request, $heroId) 
        {    
            $p=259;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }
        
    /**
    * @Route("/page260/{heroId}", name="page_260")
    */
        public function viewPage260(Request $request, $heroId) 
        {    
            $p=260;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 27;

            // malus / bonus spécifique au combat
                $circonstance = -4;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page260.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page2602/{heroId}", name="page_2602")
    */
        public function viewPage2602(Request $request, $heroId) 
        {    
            $p=2602;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 28;

            // malus / bonus spécifique au combat
                $circonstance = -4;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page2602.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

// PAGES 261 - 270

    /**
    * @Route("/page261/{heroId}", name="page_261")
    */
        public function viewPage261(Request $request, $heroId) 
        {    
            $p=261;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page262/{heroId}", name="page_262")
    */
        public function viewPage262(Request $request, $heroId) 
        {    
            $p=262;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page263/{heroId}", name="page_263")
    */
        public function viewPage263(Request $request, $heroId) 
        {    
            $p=263;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page264/{heroId}", name="page_264")
    */
        public function viewPage264(Request $request, $heroId) 
        {    
            $p=264;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page265/{heroId}", name="page_265")
    */
        public function viewPage265(Request $request, $heroId) 
        {    
            $p=265;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page266/{heroId}", name="page_266")
    */
        public function viewPage266(Request $request, $heroId) 
        {    
            $p=266;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page267/{heroId}", name="page_267")
    */
        public function viewPage267(Request $request, $heroId) 
        {    
            $p=267;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page268/{heroId}", name="page_268")
    */
        public function viewPage268(Request $request, $heroId) 
        {    
            $p=268;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page269/{heroId}", name="page_269")
    */
        public function viewPage269(Request $request, $heroId) 
        {    
            $p=269;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page270/{heroId}", name="page_270")
    */
        public function viewPage270(Request $request, $heroId) 
        {    
            $p=270;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 271 - 280

    /**
    * @Route("/page271/{heroId}", name="page_271")
    */
        public function viewPage271(Request $request, $heroId) 
        {    
            $p=271;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page272/{heroId}", name="page_272")
    */
        public function viewPage272(Request $request, $heroId) 
        {    
            $p=272;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page273/{heroId}", name="page_273")
    */
        public function viewPage273(Request $request, $heroId) 
        {    
            $p=273;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page274/{heroId}", name="page_274")
    */
        public function viewPage274(Request $request, $heroId) 
        {    
            $p=274;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page275/{heroId}", name="page_275")
    */
        public function viewPage275(Request $request, $heroId) 
        {    
            $p=275;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page276/{heroId}", name="page_276")
    */
        public function viewPage276(Request $request, $heroId) 
        {    
            $p=276;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page277/{heroId}", name="page_277")
    */
        public function viewPage277(Request $request, $heroId) 
        {    
            $p=277;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page278/{heroId}", name="page_278")
    */
        public function viewPage278(Request $request, $heroId) 
        {    
            $p=278;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page279/{heroId}", name="page_279")
    */
        public function viewPage279(Request $request, $heroId) 
        {    
            $p=279;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page280/{heroId}", name="page_280")
    */
        public function viewPage280(Request $request, $heroId) 
        {    
            $p=280;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }


// PAGES 281 - 290

    /**
    * @Route("/page281/{heroId}", name="page_281")
    */
        public function viewPage281(Request $request, $heroId) 
        {    
            $p=281;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page282/{heroId}", name="page_282")
    */
        public function viewPage282(Request $request, $heroId) 
        {    
            $p=282;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page283/{heroId}", name="page_283")
    */
        public function viewPage283(Request $request, $heroId) 
        {    
            $p=283;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 2;

            // malus / bonus spécifique au combat
                
                    $circonstance = 283;
                

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];
                
            return $this->render('pages/page283.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page284/{heroId}", name="page_284")
    */
        public function viewPage284(Request $request, $heroId) 
        {    
            $p=284;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page285/{heroId}", name="page_285")
    */
        public function viewPage285(Request $request, $heroId) 
        {    
            $p=285;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page286/{heroId}", name="page_286")
    */
        public function viewPage286(Request $request, $heroId) 
        {    
            $p=286;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page287/{heroId}", name="page_287")
    */
        public function viewPage287(Request $request, $heroId) 
        {    
            $p=287;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page288/{heroId}", name="page_288")
    */
        public function viewPage288(Request $request, $heroId) 
        {    
            $p=288;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page289/{heroId}", name="page_289")
    */
        public function viewPage289(Request $request, $heroId) 
        {    
            $p=289;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page290/{heroId}", name="page_290")
    */
        public function viewPage290(Request $request, $heroId) 
        {    
            $p=290;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }
        
// PAGES 291 - 300

    /**
    * @Route("/page291/{heroId}", name="page_291")
    */
        public function viewPage291(Request $request, $heroId) 
        {    
            $p=291;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page292/{heroId}", name="page_292")
    */
        public function viewPage292(Request $request, $heroId) 
        {    
            $p=292;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page293/{heroId}", name="page_293")
    */
        public function viewPage293(Request $request, $heroId) 
        {    
            $p=293;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page294/{heroId}", name="page_294")
    */
        public function viewPage294(Request $request, $heroId) 
        {    
            $p=294;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page295/{heroId}", name="page_295")
    */
        public function viewPage295(Request $request, $heroId) 
        {    
            $p=295;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page296/{heroId}", name="page_296")
    */
        public function viewPage296(Request $request, $heroId) 
        {    
            $p=296;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page297/{heroId}", name="page_297")
    */
        public function viewPage297(Request $request, $heroId) 
        {    
            $p=297;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page298/{heroId}", name="page_298")
    */
        public function viewPage298(Request $request, $heroId) 
        {    
            $p=298;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page299/{heroId}", name="page_299")
    */
        public function viewPage299(Request $request, $heroId) 
        {    
            $p=299;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page300/{heroId}", name="page_300")
    */
        public function viewPage300(Request $request, $heroId) 
        {    
            $p=300;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 301 - 310

    /**
    * @Route("/page301/{heroId}", name="page_301")
    */
        public function viewPage301(Request $request, $heroId) 
        {    
            $p=301;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page302/{heroId}", name="page_302")
    */
        public function viewPage302(Request $request, $heroId) 
        {    
            $p=302;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page303/{heroId}", name="page_303")
    */
        public function viewPage303(Request $request, $heroId) 
        {    
            $p=303;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page304/{heroId}", name="page_304")
    */
        public function viewPage304(Request $request, $heroId) 
        {    
            $p=304;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page305/{heroId}", name="page_305")
    */
        public function viewPage305(Request $request, $heroId) 
        {    
            $p=305;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page306/{heroId}", name="page_306")
    */
        public function viewPage306(Request $request, $heroId) 
        {    
            $p=306;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page307/{heroId}", name="page_307")
    */
        public function viewPage307(Request $request, $heroId) 
        {    
            $p=307;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

        //    $isEchange= $this->echange->Echange($heroId);
                
            return $view;
        }

    /**
    * @Route("/page308/{heroId}", name="page_308")
    */
        public function viewPage308(Request $request, $heroId) 
        {    
            $p=308;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page309/{heroId}", name="page_309")
    */
        public function viewPage309(Request $request, $heroId) 
        {    
            $p=309;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page310/{heroId}", name="page_310")
    */
        public function viewPage310(Request $request, $heroId) 
        {    
            $p=310;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 311 - 320

    /**
    * @Route("/page311/{heroId}", name="page_311")
    */
        public function viewPage311(Request $request, $heroId) 
        {    
            $p=311;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page312/{heroId}", name="page_312")
    */
        public function viewPage312(Request $request, $heroId) 
        {    
            $p=312;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page313/{heroId}", name="page_313")
    */
        public function viewPage313(Request $request, $heroId) 
        {    
            $p=313;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];                       

            return $view;
        }

    /**
    * @Route("/page314/{heroId}", name="page_314")
    */
        public function viewPage314(Request $request, $heroId) 
        {    
            $p=314;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page315/{heroId}", name="page_315")
    */
        public function viewPage315(Request $request, $heroId) 
        {    
            $p=315;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page316/{heroId}", name="page_316")
    */
        public function viewPage316(Request $request, $heroId) 
        {    
            $p=316;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page317/{heroId}", name="page_317")
    */
        public function viewPage317(Request $request, $heroId) 
        {    
            $p=317;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page318/{heroId}", name="page_318")
    */
        public function viewPage318(Request $request, $heroId) 
        {    
            $p=318;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page319/{heroId}", name="page_319")
    */
        public function viewPage319(Request $request, $heroId) 
        {    
            $p=319;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page320/{heroId}", name="page_320")
    */
        public function viewPage320(Request $request, $heroId) 
        {    
            $p=320;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 321 - 330

    /**
    * @Route("/page321/{heroId}", name="page_321")
    */
        public function viewPage321(Request $request, $heroId) 
        {    
            $p=321;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page322/{heroId}", name="page_322")
    */
        public function viewPage322(Request $request, $heroId) 
        {    
            $p=322;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page323/{heroId}", name="page_323")
    */
        public function viewPage323(Request $request, $heroId) 
        {    
            $p=323;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page324/{heroId}", name="page_324")
    */
        public function viewPage324(Request $request, $heroId) 
        {    
            $p=324;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page325/{heroId}", name="page_325")
    */
        public function viewPage325(Request $request, $heroId) 
        {    
            $p=325;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page326/{heroId}", name="page_326")
    */
        public function viewPage326(Request $request, $heroId) 
        {    
            $p=326;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page327/{heroId}", name="page_327")
    */
        public function viewPage327(Request $request, $heroId) 
        {    
            $p=327;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page328/{heroId}", name="page_328")
    */
        public function viewPage328(Request $request, $heroId) 
        {    
            $p=328;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page329/{heroId}", name="page_329")
    */
        public function viewPage329(Request $request, $heroId) 
        {    
            $p=329;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page330/{heroId}", name="page_330")
    */
        public function viewPage330(Request $request, $heroId) 
        {    
            $p=330;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

// PAGES 331 - 340

    /**
    * @Route("/page331/{heroId}", name="page_331")
    */
        public function viewPage331(Request $request, $heroId) 
        {    
            $p=331;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page332/{heroId}", name="page_332")
    */
        public function viewPage332(Request $request, $heroId) 
        {    
            $p=332;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page333/{heroId}", name="page_333")
    */
        public function viewPage333(Request $request, $heroId) 
        {   
            $p=333;         

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page334/{heroId}", name="page_334")
    */
        public function viewPage334(Request $request, $heroId) 
        {    
            $p=334;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page335/{heroId}", name="page_335")
    */
        public function viewPage335(Request $request, $heroId) 
        {    
            $p=335;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page336/{heroId}", name="page_336")
    */
        public function viewPage336(Request $request, $heroId) 
        {    
            $p=336;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 29;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page336.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page3362/{heroId}", name="page_3362")
    */
        public function viewPage3362(Request $request, $heroId) 
        {    
            $p=3362;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 30;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page3362.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page337/{heroId}", name="page_337")
    */
        public function viewPage337(Request $request, $heroId) 
        {    
            $p=337;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page338/{heroId}", name="page_338")
    */
        public function viewPage338(Request $request, $heroId) 
        {    
            $p=338;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page339/{heroId}", name="page_339")
    */
        public function viewPage339(Request $request, $heroId) 
        {    
            $p=339;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 21;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page339.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }

    /**
    * @Route("/page340/{heroId}", name="page_340")
    */
        public function viewPage340(Request $request, $heroId) 
        {    
            $p=340;
            // hors combat
                $info = $this->getInfoPage($p,$heroId ) ;

                $hero = $info["hero"];
                $items = $info["items"];
                $pagesVues = $info["pagesVues"];
                $isLunch = $info["isLunch"];
                
            //Combat
                //ennemy
                $ennemyId= 9;

            // malus / bonus spécifique au combat
                $circonstance = 0;

                $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

                $CR = $baston["CR"];
                $CS = $baston["CS"];
                $fightId = $baston["fightId"];
                $ennemy = $baston["ennemy"];

            return $this->render('pages/page340.html.twig',[
                'items'=>$items, 
                'hero'=>$hero, 
                'heroId'=>$heroId, 
                "p"=>$p, 
                'isLunch'=>$isLunch,
                'ennemy'=>$ennemy,
                "CS"=>$CS,
                "CR"=>$CR,
                'fightId'=>$fightId,
            ]);
        }    

// PAGES 341 - 351

    /**
    * @Route("/page341/{heroId}", name="page_341")
    */
        public function viewPage341(Request $request, $heroId) 
        {    
            $p=341;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page342/{heroId}", name="page_342")
    */
    public function viewPage342(Request $request, $heroId) 
    {    
        $p=342;
        // hors combat
            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 31;

        // malus / bonus spécifique au combat
            $circonstance = 0;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];

        return $this->render('pages/page342.html.twig',[
            'items'=>$items, 
            'hero'=>$hero, 
            'heroId'=>$heroId, 
            "p"=>$p, 
            'isLunch'=>$isLunch,
            'ennemy'=>$ennemy,
            "CS"=>$CS,
            "CR"=>$CR,
            'fightId'=>$fightId,
        ]);
    }

    /**
    * @Route("/page343/{heroId}", name="page_343")
    */
        public function viewPage343(Request $request, $heroId) 
        {    
            $p=343;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page344/{heroId}", name="page_344")
    */
        public function viewPage344(Request $request, $heroId) 
        {    
            $p=344;
            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                
            return $view;
        }

    /**
    * @Route("/page345/{heroId}", name="page_345")
    */
        public function viewPage345(Request $request, $heroId) 
        {    
            $p=345;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page346/{heroId}", name="page_346")
    */
        public function viewPage346(Request $request, $heroId) 
        {    
            $p=346;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page347/{heroId}", name="page_347")
    */
        public function viewPage347(Request $request, $heroId) 
        {    
            $p=347;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page348/{heroId}", name="page_348")
    */
        public function viewPage348(Request $request, $heroId) 
        {    
            $p=348;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page349/{heroId}", name="page_349")
    */
        public function viewPage349(Request $request, $heroId) 
        {    
            $p=349;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page350/{heroId}", name="page_350")
    */
        public function viewPage350(Request $request, $heroId) 
        {    
            $p=350;        

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];

            return $view;
        }

    /**
    * @Route("/page351/{heroId}", name="page_351")
    */
        public function viewPage351(Request $request, $heroId) 
        {
            $p=351;
            $fin= 'The End';        

            $info = $this->getInfoPage($p,$heroId ) ;
            //$view = $info["view"];
            $hero = $info["hero"];
            
            //objets de l'inventaire du héros inaccessibles
                $items =[];

            return $this->render('pages/page351.html.twig',['items'=>$items, 'hero'=>$hero, 'heroId'=>$heroId, "p"=>$fin]);
        }

    /**
    * @Route("/page352/{heroId}", name="page_352")
    */
        public function viewPage352(Request $request, $heroId) 
        {    
            $p=352;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
                

            return $view;
        }
    
/**
    * @Route("/page2000/{heroId}", name="page_2000")
    */
    public function viewPage2000(Request $request, $heroId) 
    {    
        $p=2000;       

            $info = $this->getInfoPage($p,$heroId ) ;
            $view = $info["view"];
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];

        return $this->render('pages/page2000.html.twig',[
            'items'=>$items, 
            'hero'=>$hero, 
            'heroId'=>$heroId, 
            "p"=>$p, 
            'isLunch'=>$isLunch
            
        ]);
    }
/**
    * @Route("/page2001/{heroId}", name="page_2001")
    */
    public function viewPage2001(Request $request, $heroId) 
    {    
        // hors combat   
            $p=2001;
            // hors combat        

            $info = $this->getInfoPage($p,$heroId ) ;
            $items = $info["items"];
            $hero = $info["hero"];
            $isLunch = $info["isLunch"];
            
        //Combat
            //ennemy
            $ennemyId= 3;

        // malus / bonus spécifique au combat
            $circonstance = -1;

            $baston = $this->getInfoCombat($ennemyId, $heroId, $circonstance);

            $CR = $baston["CR"];
            $CS = $baston["CS"];
            $fightId = $baston["fightId"];
            $ennemy = $baston["ennemy"];
        
        return $this->render('pages/page2001.html.twig',[
            'items'=>$items,
            'hero'=>$hero, 
            'heroId'=>$heroId,
            "p"=>$p,
            'isLunch'=>$isLunch,
            'ennemy'=>$ennemy,
            "CS"=>$CS,
            "CR"=>$CR,
            'fightId'=>$fightId,
                
        ]);
    }

/**
    * @Route("/pageCombat/{heroId}/{ennemyId}/{ennemyName}/{CS}/{CR}/{fightId}/{p}", name="page_combat")
    */
    public function viewPageCombat(Request $request, $heroId, $ennemyId, $ennemyName, $CS, $CR, $fightId,$p) 
    {   
        $pageOrigine=$p;
        $pName='combat';
        // dd($pageOrigine,$ennemyId);

        //le hero par son id
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        //objets de l'inventaire du héros
        $items = $this->inventory->inventory($heroId);

        //L'ennemi
        $ennemy= $this->entityManager->getRepository(Ennemy::class)->findById($ennemyId);
        
        
        //le combat
        $fight=$this->entityManager->getRepository(CombatEnd::class)->findById($fightId);
            
        // potion de force
            $potionS=false;
            for($i=0; $i < count($items); $i++){
                if($items[$i]->getId()==2){
                    $potionS=true;
                    $fight[0]->setPotionS(true);
                    $this->entityManager->persist($fight[0]);
                    $this->entityManager->flush();                
                }
            }   

        //potion de force bue?
            $potionS=$fight[0]->isPotionS();
            // dd($potionS);
            $potionI=null;
            for($i=0; $i < count($items); $i++){
                if($items[$i]->getId()==2){
                    $potionI=true;
                }else{
                    $potionI=false;
                }
            }
            // dd($potionS, $potionI);
            if($potionS == true && $potionI === false){
                $CS+=2;
                $CR = $CS - $ennemy[0]->getCombatSkill();
            }
        // dd($CS, $CR);
        
                $endLw= $fight[0]->getEndL();

                $endA= $fight[0]->getEndE();
                               
                $this->entityManager->persist($fight[0]);
                $this->entityManager->flush();
                // dd($fight);
                // dd($CS, $CR, $fight);
            $cT=$this->combatTables;
            $em=$this->entityManager;

    return $this->render('pages/pageCombat.html.twig',[
         
        'hero'=>$hero, 
        'heroId'=>$heroId, 
        "p"=>$pName, 
        "pageOrigine"=>$pageOrigine,
        'items'=>$items,
        'ennemy'=>$ennemy,
        "ennemyId"=>$ennemyId,
        'ennemyName'=>$ennemyName,
        "CR"=>$CR,
        'em'=>$em,
        'cT'=>$cT,
        'fightId'=>$fightId,
        'tour'=>$fight[0]->getTour(),        
        'combats'=>$fight,
        'endL'=>$endLw,
        'endE'=>$endA,
        "CS"=>$CS
        
    ]);
}

/**
    * @Route("/pageFuite/{heroId}/{ennemyId}/{ennemyName}/{CS}/{CR}/{fightId}/{p}", name="page_fuite")
    */
    public function viewPageFuite(Request $request, $heroId, $ennemyId, $ennemyName, $CS, $CR, $fightId,$p) 
    {   
        $pageOrigine=$p;
        $pName='fuite';
        // dd($pageOrigine,$ennemyId);

        //le hero par son id
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        //objets de l'inventaire du héros
        $items = $this->inventory->inventory($heroId);

        //L'ennemi
        $ennemy= $this->entityManager->getRepository(Ennemy::class)->findById($ennemyId);
        
        
        //le combat
        $fight=$this->entityManager->getRepository(CombatEnd::class)->findById($fightId);
            
             
                $endLw= $fight[0]->getEndL();

                $endA= $fight[0]->getEndE();
                               
                $this->entityManager->persist($fight[0]);
                $this->entityManager->flush();
                // dd($fight);
                // dd($CS, $CR, $fight);
            $cT=$this->combatTables;
            $em=$this->entityManager;

    return $this->render('pages/pageFuite.html.twig',[
         
        'hero'=>$hero, 
        'heroId'=>$heroId, 
        "p"=>$pName, 
        "pageOrigine"=>$pageOrigine,
        'items'=>$items,
        "ennemyId"=>$ennemyId,
        'ennemy'=>$ennemy,
        'ennemyName'=>$ennemyName,
        "CR"=>$CR,
        'em'=>$em,
        'cT'=>$cT,
        'fightId'=>$fightId,
        'tour'=>$fight[0]->getTour(),        
        'combats'=>$fight,
        'endL'=>$endLw,
        'endE'=>$endA,
        "CS"=>$CS
        
    ]);
}
//FIN
}