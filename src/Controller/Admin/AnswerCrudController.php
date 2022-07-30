<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VotesField;
use App\Entity\Answer;
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
                ->hideOnIndex(),
            AssociationField::new('answeredBy'),
            Field::new('createdAt')
                ->hideOnForm(),
            Field::new('updatedAt')
                ->onlyOnDetail()
        ];
    }
}
