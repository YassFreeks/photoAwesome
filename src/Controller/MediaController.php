<?php

namespace App\Controller;

use App\Form\MediaSearchType;
use App\Repository\MediaRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class MediaController extends AbstractController
{

    public function __construct(
        private PaginatorInterface $paginator,
        private MediaRepository $mediaRepository
    )
    {
        
    }

    #[Route('/media', name: 'app_media')]

    public function media(Request $request): Response
    {
        // SELECT * FROM media;
        $qb = $this->mediaRepository->getQbAll();
        $form = $this->createForm(MediaSearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dump($data);
            $title = $data['mediaTitle'];
            $userEmail = $data['userEmail'];
            
            if ($title !== null) {

                $qb->where('m.title LIKE :toto')
                ->setParameter("toto", "%" . $title . "%");

            }

            if ($userEmail !== null) {
                        // SELECT * FROM media WHERE 

                $qb->innerJoin("m.user", "u")
                    ->andWhere("u.email = :email")
                    ->setParameter("email", $userEmail);
            }
        }

        $pagination = $this->paginator->paginate(
            $qb,   // the query
            $request->query->getInt("page", 1), // receive the get from url, 1 in default if there is no parameters in the url
            10
        ); // number of Entities per page

        return $this->render("/media/index.html.twig", [
            "medias" => $pagination,
            "searchForm" => $form->createView()
        ]);
    }


}
