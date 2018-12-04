<?php

namespace App\Form;

use App\Entity\Atelier;
use App\Entity\Eleve;
use App\Entity\EleveAtelier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\AtelierRepository;
use Doctrine\Common\Collections\ArrayCollection;

class EleveAtelierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        	->add('atelier', EntityType::class, array(
			    'class'        => 'App:Atelier',
			    'choice_label' => 'nom',
			    'multiple'     => false,
			    'query_builder' => function(AtelierRepository $repository) {
		          return $repository->getOrderByNom();
		        }
			  ))
            ->add('question', 			TextType::class, 
            							array('required' => true))
            ->add('save', 			SubmitType::class, 
            						array('attr' => array('class' => 'save'),
            							  'label' => 'Enregistrer'))
        ;
		

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EleveAtelier::class,
            'ateliers' => new ArrayCollection()
        ]);
    }
}
