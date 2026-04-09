namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{BirthdayType, EmailType, PasswordType, RepeatedType, TelType, TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{Email, Length, NotBlank};

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenomCli', TextType::class, ['label' => 'Prénom', 'constraints' => [new NotBlank()]])
            ->add('nomCli',    TextType::class, ['label' => 'Nom',    'constraints' => [new NotBlank()]])
            ->add('mailCli',   EmailType::class, ['label' => 'Email', 'constraints' => [new NotBlank(), new Email()]])
            ->add('telCli',    TelType::class,   ['label' => 'Téléphone', 'required' => false])
            ->add('dateNaissanceCli', BirthdayType::class, ['label' => 'Date de naissance', 'required' => false, 'widget' => 'single_text'])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => ['label' => 'Mot de passe'],
                'second_options'  => ['label' => 'Confirmer'],
                'constraints'     => [new NotBlank(), new Length(['min' => 8])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Client::class]);
    }
}