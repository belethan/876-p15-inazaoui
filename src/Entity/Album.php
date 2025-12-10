<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name: 'album')]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\OneToMany(
        mappedBy: 'album',
        targetEntity: Media::class,
        cascade: ['persist', 'remove']
    )]
    private Collection $media;

    public function __construct()
    {
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @return Collection<int, Media> */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->media->contains($media)) {
            $this->media->add($media);
            $media->setAlbum($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->media->removeElement($media)) {
            if ($media->getAlbum() === $this) {
                $media->setAlbum(null);
            }
        }

        return $this;
    }
}
