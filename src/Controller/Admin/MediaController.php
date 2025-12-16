<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(
        Request $request,
        MediaRepository $mediaRepository
    ): Response {
        $page  = max(1, $request->query->getInt('page', 1));
        $limit = 25;

        $criteria = [];
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $mediaRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $mediaRepository->count($criteria);
        $totalPages = (int) ceil($total / $limit);

        return $this->render('admin/media/index.html.twig', [
            'medias'     => $medias,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $media = new Media();

        $form = $this->createForm(MediaType::class, $media, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if (!$uploadedFile) {
                $this->addFlash('danger', 'Aucun fichier n’a été envoyé.');
                return $this->redirectToRoute('admin_media_index');
            }

            // Sécurité utilisateur
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }

            // Persist pour obtenir un ID
            $em->persist($media);
            $em->flush();

            // Génération du nom définitif
            $extension = $uploadedFile->guessExtension() ?? 'jpg';
            $filename  = sprintf('%04d.%s', $media->getId(), $extension);

            $destination = $this->getParameter('upload_directory');
            $targetPath  = $destination . '/' . $filename;

            try {
                // Compression & déplacement
                $this->compressImage(
                    $uploadedFile->getPathname(),
                    $targetPath,
                    85
                );

                // Mise à jour du path
                $media->setPath('uploads/' . $filename);
                $em->flush();

                $this->addFlash('success', 'Image ajoutée avec succès.');
            } catch (\Throwable $e) {
                $this->addFlash(
                    'danger',
                    'Erreur lors du traitement de l’image : ' . $e->getMessage()
                );
            }

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete', methods: ['POST'])]
    public function delete(
        Media $media,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_media_' . $media->getId(),
            $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_media_index');
        }

        // Sécurité : un utilisateur ne supprime que ses médias
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $media->getUser() !== $this->getUser()
        ) {
            $this->addFlash('danger', 'Vous n’êtes pas autorisé à supprimer ce média.');
            return $this->redirectToRoute('admin_media_index');
        }

        $absolutePath = $this->getParameter('kernel.project_dir')
            . '/public/' . $media->getPath();

        if ($media->getPath() && file_exists($absolutePath)) {
            unlink($absolutePath);
        }

        $em->remove($media);
        $em->flush();

        $this->addFlash('success', 'Image supprimée avec succès.');

        return $this->redirectToRoute('admin_media_index');
    }

    /**
     * Compression image JPG / PNG
     */
    private function compressImage(
        string $source,
        string $destination,
        int $quality = 85
    ): void {
        $info = getimagesize($source);

        match ($info['mime']) {
            'image/jpeg' => imagejpeg(
                imagecreatefromjpeg($source),
                $destination,
                $quality
            ),
            'image/png' => imagepng(
                imagecreatefrompng($source),
                $destination,
                6
            ),
            default => throw new \RuntimeException('Format image non supporté'),
        };
    }
}
