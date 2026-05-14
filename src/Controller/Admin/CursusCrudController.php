<?php

namespace App\Controller\Admin;

use App\Entity\Cursus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class CursusCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cursus::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('name', 'Nom du cursus'),

            AssociationField::new('theme', 'Thème associé')
                ->setRequired(true), // important to link the cursus to a theme
        ];
    }
}
