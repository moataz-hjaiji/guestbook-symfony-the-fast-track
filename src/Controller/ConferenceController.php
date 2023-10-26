<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConferenceRepository;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig',[
            'conferences'=>$conferenceRepository->findAll(),
        ]);
    }
    #[Route('/conference/{slug}',name:"conference")]
    public function show(Conference $conference,CommentRepository $commentRepository,Request $request,#[Autowire("%photo_dir%")] string $photoDir):Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setConference($conference);
            if($photo = $form['photo']->getData()){
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                $photo->move($photoDir,$filename);
                $comment->setPhotoFilename($filename);
            }
            $this->em->persist($comment);
            $this->em->flush();
            return $this->redirectToRoute('conference',["slug"=>$conference->getSlug()]);
        }

        $offset = max(0,$request->query->getInt('offset'));
        $paginator = $commentRepository->getCommentPaginator($conference,$offset);
        return $this->render('conference/show.html.twig',[
            "conference"=>$conference,
            "comments"=>$paginator,
            "previous"=>$offset - CommentRepository::PAGINATOR_PER_PAGE,
            "next"=>min(count($paginator),$offset+CommentRepository::PAGINATOR_PER_PAGE),
            "form"=>$form->createView()
        ]);
    }
}
