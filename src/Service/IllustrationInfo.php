<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Illustration;

class IllustrationInfo{
    public function __construct(
        EntityManagerInterface $entityManager

    )
    {
        $this->entityManager = $entityManager;

    }

    public function illustrationInfo($id){
        $illus= $this->entityManager->getRepository(Illustration::class)->findBy(['id'=>$id]);
        $illuInfo =$illus[0];
        $illu=[];
        $name=chop( $illuInfo->getName());
        $source = chop( $illuInfo->getSrc());
        $alt = chop( $illuInfo->getAlt());
        array_push($illu, ["name"=>$name,"src"=>$source,"alt"=>$alt]);
        $illu=$illu[0];
        // dd($name, $source, $alt, $illu, $illuInfo);
        return $illu;
    }
}