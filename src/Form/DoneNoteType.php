<?php

namespace App\Form;

use App\Entity\Notes;
use App\Entity\TodoList;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DoneNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            "label" => "Title",
            "attr" => ["class" => "form-control", "placeholder" => "title", "id" => "title"]
        ])
        ->add('description', TextType::class, [
            "label" => "Description",
            "attr" => ["class" => "form-control", "placeholder" => "description", "id" => "description"]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notes::class,
        ]);
    }
}
