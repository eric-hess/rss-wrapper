<?php

namespace App\Form;

use App\Entity\Manipulator;
use App\Enum\ManipulatorAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManipulatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => \App\Enum\ManipulatorType::class,
            ])
            ->add('field')
            ->add('value')
            ->add('action', EnumType::class, [
                'class' => ManipulatorAction::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Manipulator::class,
        ]);
    }
}
