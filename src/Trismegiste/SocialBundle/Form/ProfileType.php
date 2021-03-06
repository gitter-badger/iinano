<?php

/*
 * Iinano
 */

namespace Trismegiste\SocialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

/**
 * ProfileType is a type for Profile entity
 */
class ProfileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fullName', 'text', [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 5, 'max' => 50])
                    ],
                    'attr' => ['placeholder' => 'Your full name (public)']
                ])
                ->add('location', 'text', [
                    'required' => false,
                    'attr' => ['placeholder' => "Where you're currently living"]
                ])
                ->add('dateOfBirth', 'date', [
                    'years' => range(date('Y') - 6, date('Y') - 100),
                    'empty_value' => 'Select',
                    'constraints' => new NotBlank()
                ])
                ->add('placeOfBirth', 'text', [
                    'required' => false,
                    'attr' => ['placeholder' => "Where you were born"]
                ])
                ->add('email', 'email', [
                    'attr' => ['placeholder' => "Private : a valid email used only if you've lost your password"],
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ]
                ])
                ->add('Save', 'submit');
    }

    public function getName()
    {
        return 'profile';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Trismegiste\SocialBundle\Security\Profile']);
    }

}
