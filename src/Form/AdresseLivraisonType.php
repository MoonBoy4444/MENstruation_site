<?php
// src/Form/AdresseLivraisonType.php
namespace App\Form;

use App\Entity\Adresse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, SubmitType, TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseLivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Si le client a des adresses existantes, on les propose
        if (!empty($options['adresses_existantes'])) {
            $builder->add('adresse_existante', EntityType::class, [
                'class'        => Adresse::class,
                'choices'      => $options['adresses_existantes'],
                'choice_label' => fn(Adresse $a) => $a->getRueAddr() . ', ' . $a->getCpAddr() . ' ' . $a->getVilleAddr(),
                'label'        => 'Utiliser une adresse enregistrée',
                'required'     => false,
                'placeholder'  => '— Nouvelle adresse —',
                'mapped'       => false,
            ]);
        }

        $builder
            ->add('typeAddr', ChoiceType::class, [
                'label'   => 'Type',
                'choices' => ['Domicile' => 'domicile', 'Bureau' => 'bureau', 'Autre' => 'autre'],
            ])
            ->add('rueAddr',    TextType::class, ['label' => 'Adresse'])
            ->add('villeAddr',  TextType::class, ['label' => 'Ville'])
            ->add('cpAddr',     TextType::class, ['label' => 'Code postal'])
            ->add('paysAddr',   TextType::class, ['label' => 'Pays', 'data' => 'France'])
            ->add('sauvegarder', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label'    => 'Sauvegarder cette adresse dans mon compte',
                'required' => false,
                'mapped'   => false,
            ])
            ->add('suivant', SubmitType::class, ['label' => 'Continuer →']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'          => Adresse::class,
            'adresses_existantes' => [],
        ]);
    }
}