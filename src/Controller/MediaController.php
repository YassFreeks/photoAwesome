<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaInsertType;
use App\Form\MediaSearchType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class MediaController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,

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

    #[Route('/media/add', name: 'app_media_add')]
    public function add(Request $request, SluggerInterface $slugger): Response
    {

        // Récupere l'utilisateru connecté
        // Soit une entité User (si connecté)
        // Soit null (si pas connecté)

        $user = $this->getUser();

        $uploadDirectory = $this->getParameter('upload_file');

        if($user === null){
            return $this->redirectToRoute('app_home');
        }

        $mediaEntity = new Media();
        //Je relie la media au user connecté
        $mediaEntity->setUser($user);
        //Je donne la date actuelle à mon media
        $mediaEntity->setCreatedAt(new \DateTime());

        $form = $this->createForm(MediaInsertType::class, $mediaEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // j'utilise le sluggerInterface pour créer un slug à partir du titre
            $slug = $slugger->slug($mediaEntity->getTitle());
            // je set le slug créé avant à mon media
            $mediaEntity->setSlug($slug);

            $file = $form->get('file')->getData();

            if ($file) {
                /** @var UploadFile $file  */
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();

                // je bouge le fichier dan sle dossier d'upload avec son nouveau nom
                try {
                    $file->move(
                        $this->getParameter('upload_file'),
                        $newFileName
                    );
                    //je donne le chemin du fichier à mon media
                    $mediaEntity->setFilePath($newFileName);

                }catch (FileException $e) {
                    
                }

                $this->entityManager->persist($mediaEntity);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_media');

            }
        }
        

        return $this->render('media/media.html.twig', [
            'formMedia' => $form->createView()
        ]);


    }


}
