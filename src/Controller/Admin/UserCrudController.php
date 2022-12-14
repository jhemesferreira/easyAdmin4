<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityPermission('ADMIN_USER_EDIT');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuild = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted(user::ROLE_SUPER_ADMIN)) {
            return $queryBuild;
        }

        $queryBuild
            ->andWhere('entity.id = :id')
            ->setParameter('id', $this->getUser()->getId());

        return $queryBuild;
    }

    public function configureFields(string $pageName): iterable
    {
        $roles = User::ROLES_AVAILABLES;
        if (!$this->isGranted(user::ROLE_SUPER_ADMIN)) {
            unset($roles[User::ROLE_SUPER_ADMIN]);
        }
        return [
            IdField::new('id')
                ->hideOnForm(),
                AvatarField::new('avatar')
                ->formatValue(static function ($value, ?User $user) {
                    return $user?->getAvatarUrl();
                })
                ->hideOnForm(),
            ImageField::new('avatar')
                ->setBasePath('uploads/avatars')
                ->setUploadDir('public/uploads/avatars')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->onlyOnForms(),
            TextField::new('fullName')
                ->hideOnForm(),
            TextField::new('lastName')
                ->onlyOnForms(),
            TextField::new('firstName')
                ->onlyOnForms(),

            EmailField::new('email'),
            BooleanField::new('enabled')
                ->renderAsSwitch(false),
            DateField::new('createdAt')
                ->hideOnForm(),
            ChoiceField::new('roles')
                ->setChoices(array_flip($roles))
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges(),
            // TextEditorField::new('description'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(BooleanFilter::new('enabled')->setFormTypeOption('expanded', false));
    }
}
