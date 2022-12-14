<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Faker\Core\File;
use App\Entity\Question;
use App\EasyAdmin\VotesField;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_MODERATOR')]
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            Field::new('name')
            ->setSortable(false),
            SlugField::new('slug')
                ->hideOnIndex()
                ->setTargetFieldName('name')
                ->setFormTypeOptions(['disabled' => $pageName !== Crud::PAGE_NEW]),
            TextareaField::new('question', 'Question.Admin.Form.Question.label')
                ->hideOnIndex(),
            AssociationField::new('topic'),
            VotesField::new('votes', 'Question.Admin.Form.TotalVotes.label')
                ->setHelp('Question.Admin.Form.TotalVotes.Help')
                ->setTextAlign('center')
                ->setPermission('ROLE_SUPER_ADMIN'),
            AssociationField::new('askedBy')
                ->autocomplete()
                ->formatValue(static function ($value, ?Question $question) {
                    if (!$user = $question?->getAskedBy()) {
                        return null;
                    }
                    // !!!Attention: when we rende things in EasyAdmin, we can include HTML in most situations...
                    return sprintf('%s&nbsp(%s)', $user->getEmail(), $user->getQuestions()->count());
                })
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    $queryBuilder->andWhere('entity.enabled = :enabled')
                        ->setParameter('enabled', true);
                }),
            AssociationField::new('answers')
                ->autocomplete()
                ->setFormTypeOptions(['by_reference' => false ]),
            Field::new('createdAt', 'Question.Admin.Form.CratedAt.label')
            ->hideOnForm(),
            AssociationField::new('updatedBy')
                ->onlyOnDetail(),
            BooleanField::new('isApproved')
                ->renderAsSwitch(false)
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort([
                'askedBy.enabled' => 'DESC',
                'createdAt' => 'DESC'
            ])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // return function allows to customize action on different page...
        $viewAction = function() {
            return Action::new('view')
            // ->linkToRoute() -> that would generate a URL through the admin section
            ->linkToUrl(fn (Question $question) => $this->generateUrl('app_question_show', [
                'slug' => $question->getSlug()
            ]))
            ->setLabel('View on site');
        };
        // !!! Attention
        // ->linkToUrl(
        // print args inside function
        //     fn (Question $question) =>
        //     dd(func_get_args())
        // );
        return parent::configureActions($actions)
            ->setPermissions([
                Action::INDEX => 'ROLE_MODERATOR',
                Action::DETAIL => 'ROLE_MODERATOR',
                Action::EDIT => 'ROLE_MODERATOR',
                Action::NEW => 'ROLE_SUPER_ADMIN',
                Action::DELETE => 'ROLE_SUPER_ADMIN',
                Action::BATCH_DELETE => 'ROLE_SUPER_ADMIN'
            ])
            ->add(Crud::PAGE_INDEX, $viewAction())
            ->add(Crud::PAGE_EDIT, $viewAction()->addCssClass('btn btn-success')->setIcon('fa fa-eye'));
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Currently logged in user is not an instance of User?!');
        }

        $entityInstance->setUpdatedBy($user);
        parent::updateEntity($entityManager, $entityInstance);
    }
}
