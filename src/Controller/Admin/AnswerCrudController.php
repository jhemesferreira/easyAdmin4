<?php

namespace App\Controller\Admin;

use App\Entity\Answer;
use App\EasyAdmin\VotesField;
use App\Controller\Admin\QuestionCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            Field::new('answer'),
            VotesField::new('votes')
                ->setTemplatePath('admin/field/votes.html.twig'),
            AssociationField::new('question')
                ->autocomplete()
                //Attention : it's to prevent that Ajax request be made QuestionPendingApprovalCrudController
                ->setCrudController(QuestionCrudController::class)
                ->hideOnIndex(),
            AssociationField::new('answeredBy'),
            Field::new('createdAt')
                ->hideOnForm(),
            Field::new('updatedAt')
                ->onlyOnDetail()
        ];
    }
}
