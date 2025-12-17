<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests")]
    public function guests(ManagerRegistry $doctrine): Response
    {
        $userRepo = $doctrine->getRepository(User::class);

        // Utilise la méthode optimisée du repository
        $guests = $userRepo->findAllGuests();
        //dd($guests);
        return $this->render('front/guests.html.twig', compact('guests'));
    }

    #[Route("/guest/{id}", name: "guest")]
    public function guest(int $id, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $guest = $em->getRepository(User::class)->find($id);
        return $this->render('front/guest.html.twig', compact('guest'));
    }

    #[Route("/portfolio/{id?}", name: "portfolio")]
    public function portfolio(ManagerRegistry $doctrine, int|null $id = null): Response
    {
        $albumRepo = $doctrine->getRepository(Album::class);
        $mediaRepo = $doctrine->getRepository(Media::class);
        $userRepo = $doctrine->getRepository(User::class);

        // Tous les albums
        $albums = $albumRepo->findAll();

        // Album sélectionné (ou null pour la page générale)
        $album = $id ? $albumRepo->find($id) : null;

        // Administratrice (Ina)
        $admin = $userRepo->findAdmin();

        // Sélection des médias en fonction du contexte
        if ($album) {
            $medias = $mediaRepo->findBy(compact('album'));
        } else {
            $medias = $mediaRepo->findBy(['user' => $admin]);
        }

        return $this->render('front/portfolio.html.twig', compact('albums', 'album', 'medias'));
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
