<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['required' => true, 'label' => 'Email', 'attr' => []])
            ->add('nom', TextType::class, ['required' => true, 'label' => 'Last name', 'attr' => []])
            ->add('prenom', TextType::class, ['required' => true, 'label' => 'First name', 'attr' => []])
            ->add('adresse', TextType::class, ['required' => true, 'label' => 'Adress', 'attr' => []])
            ->add('ville', TextType::class, ['required' => true, 'label' => 'City', 'attr' => []])
            ->add('codePostal', TextType::class, ['required' => true, 'label' => 'Postal code', 'attr' => []])
            ->add('province', ChoiceType::class, ['choices' => ['BC' => 'BC', 'QC' => 'QC', 'AB' => 'AB', 'MB' => 'MB', 'NB' => 'NB',
                                                                'NL' => 'NL', 'NT' => 'NT', 'NS' => 'NS', 'NU' => 'NU', 'ON' => 'ON',
                                                                'PE' => 'PE', 'SK' => 'SK', 'YT' => 'YT']], ['required' => true, 'label' => 'Province', 'attr' => []])
            ->add('phone', TextType::class, ['required' => false, 'label' => 'Phone', 'attr' => []])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "Both passwords must be indentical.",
                'constraints' => [new Assert\Length(min: 6, minMessage: "The password must have a minimum of {{ limit }} characters.")],
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => "Password"],
                'second_options' => ['label' => "Password confirmation"]
            ])
            ->add('create', SubmitType::class, [
                'label' => "Create your account",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btnCreate btn-primary']
            ]);


            $builder->get('phone')->addModelTransformer(new CallbackTransformer(
                function($phoneFromDatabase) {
                    $newPhone = substr_replace($phoneFromDatabase, "-", 3, 0);
                    return substr_replace($newPhone, "-", 7, 0);
                }, 
                function ($phoneFromView) {
                    return str_replace("-", "", $phoneFromView);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
