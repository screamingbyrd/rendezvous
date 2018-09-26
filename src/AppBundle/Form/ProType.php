<?php

namespace AppBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ProType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => 'form.registration.email',
                'constraints' => array(
                    new EMail(),
                )
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array(
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array(
                        'autocomplete' => 'new-password',
                    ),
                ),
                'constraints' => array(
                    new Length(array('min' => 6)),
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*).*$/'),
                ),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
//            ->add('terms',CheckboxType::class, array('mapped' => false,
//                'label'    => 'form.registration.accept',
//                'constraints' => array(new NotNull())))
            ->add('firstName',TextType::class, array(
                'required' => true,
                'label' => 'form.registration.firstname'
            ))
            ->add('lastName', TextType::class, array(
                'required' => true,
                'label' => 'form.registration.lastname'
            ))

            ->add('name', TextType::class, array(
                'required' => true,
                'label' => 'form.registration.companyName'
            ))

            ->add('description', TextareaType::class, array(
                'required' => false,
                'label' => 'form.registration.description',
            ))

            ->add('location', TextType::class,array(
                'attr' => array('class' => 'form-control'),
                'required' => false,
                ))

            ->add('city', TextType::class,array(
                'attr' => array('class' => 'form-control'),
                'required' => false,
                ))

            ->add('zipcode', TextType::class,array(
                'attr' => array('class' => 'form-control'),
                'required' => false,
                ))

            ->add('phone', TelType::class, array(
                'required' => true,
                'label' => 'form.registration.phone'
            ))

            ->add('images', CollectionType::class, array(

                'entry_type' => ImageType::class,

                'entry_options' => array('label' => false),
                'attr' => array(
                    'class' => 'my-selector'
                ),
                'required' => false,
                'allow_add' => true,
                'allow_delete'=>true,
            ))
            ->add('kind', ChoiceType::class, array(
                'choices' => array(
                    'form.registration.type1' => 'form.registration.type1',
                    'form.registration.type2' => 'form.registration.type2',
                    'form.registration.type3' => 'form.registration.type3',
                    'form.registration.type4' => 'form.registration.type4',
                    'form.registration.type5' => 'form.registration.type5',
                ),
                'required' => false,
                'label' => 'form.registration.type',
                'attr' => array(
                    'class' => 'select2'),
            ))
            ->add('submit',      SubmitType::class, array(
                'attr' => array(
                    'class' => 'rendezvous-button login',
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Pro'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pro';
    }


}
