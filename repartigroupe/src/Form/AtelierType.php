<?php

namespace App\Form;

use App\Entity\Atelier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AtelierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('nom', 			TextType::class,array('required' => true))
            ->add('numero', 		TextType::class, 
            						array('required' => true))
            ->add('titre', 			TextType::class, 
            						array('required' => true))
            //->add('nbparticipant', 	TextType::class,array('required' => true))
            ->add('save', 			SubmitType::class, 
            						array('attr' => array('class' => 'save'),
            							  'label' => 'Enregistrer'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Atelier::class,
        ]);
    }
}
