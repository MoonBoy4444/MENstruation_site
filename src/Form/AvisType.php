namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextareaType, TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreAvis', TextType::class, ['label' => 'Titre'])
            ->add('noteAvis', ChoiceType::class, [
                'label'   => 'Note',
                'choices' => ['⭐' => 1, '⭐⭐' => 2, '⭐⭐⭐' => 3, '⭐⭐⭐⭐' => 4, '⭐⭐⭐⭐⭐' => 5],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('msgAvis', TextareaType::class, ['label' => 'Votre avis', 'attr' => ['rows' => 5]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Avis::class]);
    }
}