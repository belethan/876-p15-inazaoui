<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ fichier (non mappé à l'entité)
            ->add('file', FileType::class, [
                'label' => 'Image',
                'mapped' => false, //  OBLIGATOIRE
                'required' => true,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png'],
                        mimeTypesMessage: 'Veuillez sélectionner une image JPG ou PNG valide'
                    )
                ],
            ])

            // Titre du média
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ]);

        // Champs visibles uniquement pour l'administrateur
        if ($options['is_admin'] === true) {
            $builder
                ->add('user', EntityType::class, [
                    'label' => 'Utilisateur',
                    'class' => User::class,
                    'required' => false,
                    'choice_label' => 'email', // 🔎 plus fiable que "name"
                    'placeholder' => '— Sélectionner un utilisateur —',
                ])
                ->add('album', EntityType::class, [
                    'label' => 'Album',
                    'class' => Album::class,
                    'required' => false,
                    'choice_label' => 'name',
                    'placeholder' => '— Aucun album —',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
        ]);

        $resolver->setAllowedTypes('is_admin', 'bool');
    }
}
