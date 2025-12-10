<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests")]
    public function guests(ManagerRegistry $doctrine)
    {
        $userRepo = $doctrine->getRepository(User::class);

        // Utilise la méthode optimisée du repository
        $guests = $userRepo->findAllGuests();
        //dd($guests);
        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route("/guest/{id}", name: "guest")]
    public function guest(int $id, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $guest = $em->getRepository(User::class)->find($id);
        $guest = $doctrine->getRepository(User::class)->find($id);
        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route("/portfolio/{id?}", name: "portfolio")]
    public function portfolio(ManagerRegistry $doctrine, ?int $id = null)
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
            $medias = $mediaRepo->findBy(['album' => $album]);
        } else {
            $medias = $mediaRepo->findBy(['user' => $admin]);
        }

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album'  => $album,
            'medias' => $medias,
        ]);
    }

    #[Route("/about", name: "about")]
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}
