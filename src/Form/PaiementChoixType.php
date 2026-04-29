<?php
// src/Form/PaiementChoixType.php
namespace App\Form;

use App\Entity\Paiement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementChoixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paiement', EntityType::class, [
                'class'        => Paiement::class,
                'choice_label' => 'libellePay',
                'label'        => 'Mode de paiement',
                'expanded'     => true,
                'multiple'     => false,
            ])
            ->add('confirmer', SubmitType::class, [
                'label' => '✓ Confirmer la commande',
                'attr'  => ['class' => 'btn btn-success btn-lg'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['mapped' => false]);
    }
}