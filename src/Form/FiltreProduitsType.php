<?php
// src/Form/FiltreProduitsType.php
namespace App\Form;

use App\Entity\TypeProduit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, MoneyType, SearchType, SubmitType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreProduitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', SearchType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Rechercher...']])
            ->add('type', EntityType::class, [
                'class'        => TypeProduit::class,
                'choice_label' => 'nomTypeProd',
                'label'        => 'Catégorie',
                'required'     => false,
                'placeholder'  => 'Toutes catégories',
            ])
            ->add('prixMin', MoneyType::class, ['label' => 'Prix min', 'required' => false, 'currency' => 'EUR'])
            ->add('prixMax', MoneyType::class, ['label' => 'Prix max', 'required' => false, 'currency' => 'EUR'])
            ->add('sort', ChoiceType::class, [
                'label'   => 'Trier par',
                'choices' => ['Nom' => 'nomProd', 'Prix croissant' => 'prixProd_asc', 'Prix décroissant' => 'prixProd_desc'],
                'required' => false,
            ])
            ->add('filtrer', SubmitType::class, ['label' => 'Filtrer']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['method' => 'GET', 'csrf_protection' => false]);
    }
}