<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ...
            ->add('document', FileType::class, array(
            		'help' => 'Chargez le fichier généré par Google Form',
            		'required'=> true
            	  ))
            ->add('save', SubmitType::class, array(
            		'attr' => array('class' => 'save'),
            		'label' => 'Charger le fichier'
            	  ))
            // ...
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Document::class,
        ));
    }
}
//The option "label_document" does not exist. Defined options are: "action", "allow_extra_fields", "attr", "auto_initialize", "block_name", "by_reference", "compound", "constraints", "data", "data_class", "disabled", "empty_data", "error_bubbling", "error_mapping", "extra_fields_message", "help", "inherit_data", "invalid_message", "invalid_message_parameters", "label", "label_attr", "label_format", "mapped", "method", "multiple", "post_max_size_message", "property_path", "required", "translation_domain", "trim", "upload_max_size_message", "validation_groups".