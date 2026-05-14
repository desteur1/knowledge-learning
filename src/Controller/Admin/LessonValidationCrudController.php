<?php

namespace App\Controller\Admin;

use App\Entity\LessonValidation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class LessonValidationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LessonValidation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('user', 'Utilisateur'),

            AssociationField::new('lesson', 'Leçon'),

            DateTimeField::new('validatedAt', 'Validé le')
                ->hideOnForm(),
        ];
    }
}