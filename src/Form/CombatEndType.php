<?php

namespace App\Form;

use App\Entity\CombatEnd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CombatEndType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endL', IntegerType::class,['label'=>'Endurance'])
            ->add('endE',IntegerType::class,['label'=>'Endurance Ennemi'])
            ->add('tour',IntegerType::class,['label'=>'tour'])
            ->add('combattre',SubmitType::class,[
                'label'=>'Fight',
                'attr'=>['class'=>"btn", 'id'=>"tour"]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CombatEnd::class,
        ]);
    }
}
