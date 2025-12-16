<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AlbumController extends AbstractController
{
    #[Route('/admin/album', name: 'admin_album_index')]
    public function index(
        AlbumRepository $albumRepository
    ): Response {
        return $this->render('admin/album/index.html.twig', [
            'albums' => $albumRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/admin/album/add', name: 'admin_album_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $album = new Album();

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($album);
            $em->flush();

            $this->addFlash('success', 'Album créé avec succès.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/album/update/{id}', name: 'admin_album_update')]
    public function update(
        Request $request,
        Album $album,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Album mis à jour avec succès.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }

    #[Route(
        '/admin/album/delete/{id}',
        name: 'admin_album_delete',
        methods: ['POST']
    )]
    public function delete(
        Album $album,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_album_' . $album->getId(),
            $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_album_index');
        }

        $em->remove($album);
        $em->flush();

        $this->addFlash('success', 'Album supprimé avec succès.');

        return $this->redirectToRoute('admin_album_index');
    }
}
