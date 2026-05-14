<?php

namespace App\Controller\Admin;

use App\Entity\Lesson;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class LessonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lesson::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('name', 'Titre de la leçon'),

            TextareaField::new('content', 'Contenu')
                ->hideOnIndex(),

            UrlField::new('videoUrl', 'Lien YouTube')
                ->setHelp('Exemple : https://www.youtube.com/watch?v=xxxx')
                ->hideOnIndex(),

            ImageField::new('videoPath', 'Vidéo uploadée')
                ->setUploadDir('public/uploads/videos')
                ->setBasePath('/uploads/videos')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false)
                ->hideOnIndex(),

            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR'),

            AssociationField::new('cursus', 'Cursus associé')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'choice_label' => 'name'
                ])
                ->autocomplete(),

            IntegerField::new('position')->setSortable(true),
        ];
    }
}
