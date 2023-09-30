<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\When;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailOptions = [];

        if ($options['email_required'] === false) {
            $emailOptions['required'] = false;
        }

        $plainPasswordConstraints = [
            new NotBlank([
                'message' => 'Please enter a password',
            ]),
            new Length([
                'min' => 6,
                'minMessage' => 'Your password should be at least {{ limit }} characters',
                // max length allowed by Symfony for security reasons
                'max' => 4096,
            ]),
        ];
        $plainPasswordOptions = [
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
        ];

        if ($options['plain_password_required'] === false) {
            $plainPasswordOptions['required'] = false;
        } else {
            $plainPasswordOptions['constraints'] = $plainPasswordConstraints;
        }

        $builder
            ->add('email', EmailType::class, $emailOptions)
            ->add('plainPassword', PasswordType::class, $plainPasswordOptions)
        ;

        if ($options['show_roles']) {
            $builder->add('roles', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => ['label' => false],
            ]);
        }

        if ($options['show_confirm_current_password'] === true) {
            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) use ($options, $plainPasswordOptions, $plainPasswordConstraints): void {
            $newPlainPasswordOptions = $plainPasswordOptions;

            if (!$options['plain_password_required'] && !empty($formEvent->getData()['plainPassword'])) {
                $newPlainPasswordOptions['constraints'] = $plainPasswordConstraints;
                $newPlainPasswordOptions['required'] = false;
            }

            $formEvent->getForm()->add('plainPassword', PasswordType::class, $newPlainPasswordOptions);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'email_required' => true,
            'plain_password_required' => true,
            'show_confirm_current_password' => false,
            'show_roles' => false,
        ]);
    }
}
