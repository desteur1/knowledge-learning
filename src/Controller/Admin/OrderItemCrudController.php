<?php

namespace App\Controller\Admin;

use App\Entity\OrderItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;


class OrderItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('order', 'Commande'),
            AssociationField::new('lesson', 'Leçon'),

            MoneyField::new('price', 'Prix')->setCurrency('EUR'),

            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Modifié le')->onlyOnDetail(),

            AssociationField::new('createdBy', 'Créé par')->onlyOnDetail(),
            AssociationField::new('updatedBy', 'Modifié par')->onlyOnDetail(),
        ];
    }
}
