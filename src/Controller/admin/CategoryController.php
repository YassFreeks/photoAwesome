<?php

namespace App\Controller\admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/category')]
class CategoryController extends AbstractController
{

    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager


    )
    {
        
    }


    #[Route('/', name: 'app_category')]
    public function index(): Response
    {

        $categoryEntities = $this->categoryRepository->findAll();


        return $this->render('category/index.html.twig', [
            'categories' => $categoryEntities
        ]);
    }

    #[Route('/show/{id}', name: 'app_category_show')]
    public function detail($id): Response
    {
        
        $categoryEntitie = $this->categoryRepository->find($id);

        if ($categoryEntitie === null) {
            return $this->redirectToRoute('app_home');
        }


        return $this->render('category/show.html.twig', [
            'category' => $categoryEntitie
        ]);
    }

    #[Route('/add', name: 'app_category_show')]
    public function add(Request $request): Response
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }



        return $this->render('category/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
