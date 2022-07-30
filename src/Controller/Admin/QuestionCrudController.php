<?php

namespace App\Controller\Admin;

use Faker\Core\File;
use App\Entity\Question;
use App\EasyAdmin\VotesField;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

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
            Field::new('name'),
            SlugField::new('slug')
                ->hideOnIndex()
                ->setTargetFieldName('name')
                ->setFormTypeOptions(['disabled' => $pageName !== Crud::PAGE_NEW]),
            TextareaField::new('question', 'Question.Admin.Form.Question.label')
                ->hideOnIndex(),
            AssociationField::new('topic'),
            VotesField::new('votes', 'Question.Admin.Form.TotalVotes.label')
                ->setHelp('Question.Admin.Form.TotalVotes.Help')
                ->setTextAlign('center'),
            AssociationField::new('askedBy')
                ->autocomplete()
                ->formatValue(static function ($value, Question $question) {
                    if (!$user = $question->getAskedBy()) {
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
            ->hideOnForm()
        ];
    }
}
