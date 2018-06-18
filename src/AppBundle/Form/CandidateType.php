<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;


class CandidateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('email',      EmailType::class, array(
                'required' => true,
                'label' => 'form.registration.email',



            ))
            ->add('firstName',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.firstname',
                ))
            ->add('lastName',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.lastname'

            ))


            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array(
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array(
                        'autocomplete' => 'new-password',
                    ),
                ),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))

            ->add('description',      TextareaType::class, array(
                'required' => false,
                'label' => 'form.registration.description'
                ))

            ->add('age',      TextType::class, array(
                'required' => false,
                'label' => 'form.registration.age'
            ))

            ->add('experience', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',
            ),
                'required' => false,
                'attr' => array('class' => 'select2'),
            ))
            ->add('license', ChoiceType::class, array('choices' => array(
                'form.registration.lis1' => 'form.registration.lis1',
                'form.registration.lis2' => 'form.registration.lis2',
                'form.registration.lis3' => 'form.registration.lis3',
                'form.registration.lis4' => 'form.registration.lis4',
                'form.registration.lis5' => 'form.registration.lis5',
                'form.registration.lis6' => 'form.registration.lis6',
                'form.registration.lis7' => 'form.registration.lis7',
                'form.registration.lis8' => 'form.registration.lis8',
                'form.registration.lis9' => 'form.registration.lis9',
                'form.registration.lis10' => 'form.registration.lis10',
                'form.registration.lis11' => 'form.registration.lis11',
                'form.registration.lis12' => 'form.registration.lis12',
                'form.registration.lis13' => 'form.registration.lis13',
                'form.registration.lis14' => 'form.registration.lis14',
            ),

                'multiple' => true,
                'required' => false,
                'attr' => array('class' => 'select2'),
                'label' => 'form.registration.license',
            ))

            ->add('searchedtag', EntityType::class, array(
                'required' => false,
                'label' => 'form.registration.searchedtag',
                'class' => Tag::class,
                'choice_label' =>  'name',
                'placeholder' => 'form.registration.searchedtagPH',
                'multiple' => true,
                'attr' => array('class' => 'select2'),

            ))

            ->add('tag', EntityType::class, array(
                'required' => false,
                'class' => Tag::class,
                'label' => 'form.registration.tag',
                'choice_label' =>  'name',
                'multiple' => true,
                'attr' => array('class' => 'select2'),
                'placeholder' => 'form.registration.tagPH',

            ))


            ->add('diploma', ChoiceType::class, array('choices' => array(
                'form.registration.dip1' => 'form.registration.dip1',
                'form.registration.dip2' => 'form.registration.dip2',
                'form.registration.dip3' => 'form.registration.dip3',
                'form.registration.dip4' => 'form.registration.dip4',
                'form.registration.dip5' => 'form.registration.dip5',
            ),
                'placeholder' => 'form.registration.dip0',
            ))


            ->add('socialMedia',      TextType::class, array('required' => false))
            ->add('phone',      TextType::class, array('required' => true))
            ->add('save',      SubmitType::class)
            ->getForm()
        ;
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Candidate'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_candidate';
    }


}
