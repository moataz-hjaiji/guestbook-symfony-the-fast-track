<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConferenceRepository;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Environment $twig,ConferenceRepository $conferenceRepository): Response
    {
//         return new Response(<<<EOF
//           <html lang="en">
//                <body>
//                    <img src="/images/under-construction.gif" />
//               </body>
//            </html>
//           EOF
//        );
        return new Response($twig->render('conference/index.html.twig',[
            'conferences'=>$conferenceRepository->findAll(),
        ]));
    }
    #[Route('/conference/{id}',name:"conference")]
    public function show(Conference $conference,CommentRepository $commentRepository):Response
    {
        return $this->render('conference/show.html.twig',[
            "conference"=>$conference,
            "comments"=>$commentRepository->findBy(['conference'=>$conference],['createdAt'=>"DESC"])
        ]);
    }
}
