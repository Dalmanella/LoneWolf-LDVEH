<?php

namespace App\Form;

use App\Entity\Hero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewAdventureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('name', TextType::class, [
                'required'=> true, 
                'label'=> "Votre héros", 
                'attr'=>[
                    'placeholder'=>'Nom',
                    'style'=>"font-size:5rem;"
                ]])
            
            ->add('combatSkill', IntegerType::class, [
                'label'=> "Combat Skill",
                
            ])

            ->add('endurance', IntegerType::class, [
                'label'=> "Endurance" ])

            ->add('endMax', IntegerType::class)

            ->add('gold', IntegerType::class, ['required'=>false])
            ->add('page', IntegerType::class, ['required'=>false, 'empty_data' =>''])

            ->add('weapon', IntegerType::class ,[
                'required'=>false,
                'empty_data' =>'11'
                ])

            ->add('specialBag',IntegerType::class, ['required'=>false])
            ->add('backpack',IntegerType::class, ['required'=>false])
            

            ->add('kaiSix',CheckboxType::class, [
                'label'=> "Sixth sens", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount  ',
                    'onclick'=>'updateCount();',
                    
                ]])
            
            ->add('kaiTrack',CheckboxType::class, [
                'label'=> "Tracking", 
                'required'=>false,
                'attr'=>[
                    'class'=>'kaicount ',
                    'onclick'=>'updateCount();',
                ]])
            
            ->add('kaiHeal',CheckboxType::class, [
                'label'=> "Healing", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount ',
                    'onclick'=>'updateCount();',
                ]])
            
            ->add('kaiWeapon',CheckboxType::class, [
                'label'=> "Weapon mastery", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount ',
                    'onclick'=>'updateCount(); kaiWeaponCheck();'
                ]])

            ->add('kaiMShield',CheckboxType::class, [
                'label'=> "Mindshield", 
                'required'=>false,
                'attr'=>[
                    'class'=>'kaicount  ',
                    'onclick'=>'updateCount();',
                ]])

            ->add('kaiMBlast',CheckboxType::class, [
                'label'=> "Mindblast", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount  ',
                    'onclick'=>'updateCount();',
                ]])
            ->add('kaiAnimal',CheckboxType::class, [
                'label'=> "Animal kinship", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount  ',
                    'onclick'=>'updateCount();',
                ]])
            ->add('kaiMoM',CheckboxType::class, [
                'label'=> "Mind over matter", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount check-label',
                    'onclick'=>'updateCount();',
                ]])
            ->add('kaiCamou',CheckboxType::class, [
                'label'=> "Camouflage", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount  ',
                    'onclick'=>'updateCount();',
                ]])
            ->add('kaiHunt',CheckboxType::class, [
                'label'=> "Hunting", 
                'required'=>false, 
                'attr'=>[
                    'class'=>'kaicount ',
                    'onclick'=>'updateCount();',
            ]])

            // ->add('userId', IntegerType::class, ['attr'=>['value'=>2]])

            ->add('validation', SubmitType::class,[
                'label'=>false,
                'attr'=>[
                    'class'=>"btn btn-primary fs-2 "                   
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hero::class,
        ]);
    }
}
