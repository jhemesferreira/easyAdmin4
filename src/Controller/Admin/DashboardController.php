<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Answer;
use App\Entity\Question;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    private $questionRepository;
    private $chartBuilder;
    public function __construct(QuestionRepository $questionRepository, ChartBuilderInterface $chartBuilder)
    {
        $this->questionRepository = $questionRepository;
        $this->chartBuilder = $chartBuilder;
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'latestQuestions' => $this->questionRepository->findLatest(),
            'topVoted' => $this->questionRepository->findTopVoted(),
            'chart' => $this->createChart()
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cauldron Overflow Admin');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDefaultSort([
                'id'=> 'DESC'
            ])
            ->overrideTemplate('crud/field/id', 'admin/field/id_with_icon.html.twig')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Questions', 'fa fa-question', Question::class);
        yield MenuItem::linkToCrud('Answers', 'fa fa-comments', Answer::class);
        yield MenuItem::linkToCrud('topics', 'fa fa-folder', Topic::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToUrl('Home', 'fa fa-home', $this->generateUrl('app_homepage'));
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user');
        }

        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatarUrl())
            ->setMenuItems([
                MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl('app_profile_show'))
            ]);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
        ->addWebpackEncoreEntry('admin');
    }

    private function createChart(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                   'suggestedMin' => 0,
                   'suggestedMax' => 100,
                ],
            ],
        ]);
        return $chart;
    }
}
