<?php
// src/Form/LivraisonChoixType.php
namespace App\Form;

use App\Entity\Livraison;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivraisonChoixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('livraison', EntityType::class, [
                'class'        => Livraison::class,
                'choice_label' => fn(Livraison $l) => sprintf(
                    '%s — %s jour(s) — %s €',
                    $l->getNomLivr(),
                    $l->getDelaiLivr() ?? '?',
                    number_format((float)($l->getFraisLivr() ?? 0), 2, ',', ' ')
                ),
                'label'    => 'Mode de livraison',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('suivant', SubmitType::class, ['label' => 'Continuer →']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['mapped' => false]);
    }
}