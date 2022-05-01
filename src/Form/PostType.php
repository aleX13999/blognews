<?php

namespace App\Form;

use App\Entity\Posts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('header')
        ->add('imgFile', FileType::class,[
            'label' => 'Выберите изображение',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                ])
            ],
        ])
        ->add('annotation', TextareaType::class)
        ->add('date', DateTimeType::class, [
            'date_label' => 'Установите дату новости',
            'widget' => 'single_text',

        ])
        ->add('alltext', TextareaType::class)
        ->add('save', SubmitType::class,[
            'label' => 'Сохранить',
        ])
        ->add('isVisible', CheckboxType::class, [
            'label'    => 'Показывать новость',
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
