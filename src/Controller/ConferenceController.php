<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConferenceRepository;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
//         return new Response(<<<EOF
//           <html lang="en">
//                <body>
//                    <img src="/images/under-construction.gif" />
//               </body>
//            </html>
//           EOF
//        );
        return $this->render('conference/index.html.twig',[
            'conferences'=>$conferenceRepository->findAll(),
        ]);
    }
    #[Route('/conference/{id}',name:"conference")]
    public function show(Conference $conference,CommentRepository $commentRepository,Request $request):Response
    {
        $offset = max(0,$request->query->getInt('offset'));
        $paginator = $commentRepository->getCommentPaginator($conference,$offset);

        return $this->render('conference/show.html.twig',[
            "conference"=>$conference,
            "comments"=>$paginator,
            "previous"=>$offset - CommentRepository::PAGINATOR_PER_PAGE,
            "next"=>min(count($paginator),$offset+CommentRepository::PAGINATOR_PER_PAGE)
        ]);
    }
}
