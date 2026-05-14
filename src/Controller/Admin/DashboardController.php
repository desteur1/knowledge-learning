<?php

namespace App\Controller\Admin;


use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\ThemeCrudController;
use App\Controller\Admin\CursusCrudController;
use App\Controller\Admin\LessonCrudController;
use App\Controller\Admin\OrderCrudController;
use App\Controller\Admin\OrderItemCrudController;
use App\Controller\Admin\LessonValidationCrudController;
use App\Controller\Admin\CertificationCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Knowledge Learning');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user');

        yield MenuItem::linkTo(ThemeCrudController::class, 'Thèmes', 'fa fa-folder');

        yield MenuItem::linkTo(CursusCrudController::class, 'Cursus', 'fa fa-book');

        yield MenuItem::linkTo(LessonCrudController::class, 'Leçons', 'fa fa-play');

        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fa fa-shopping-cart');

        yield MenuItem::linkTo(OrderItemCrudController::class, 'Items achetés', 'fa fa-list');

        yield MenuItem::linkTo(LessonValidationCrudController::class, 'Validations', 'fa fa-check');

        yield MenuItem::linkTo(CertificationCrudController::class, 'Certifications', 'fa fa-graduation-cap');
    }
}