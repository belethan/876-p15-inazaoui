<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'media')]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int|null $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private string|null $path = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User|null $user = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(name: 'album_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private Album|null $album = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getPath(): string|null
    {
        return $this->path;
    }

    public function setPath(string|null $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getUser(): User|null
    {
        return $this->user;
    }

    public function setUser(User|null $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAlbum(): Album|null
    {
        return $this->album;
    }

    public function setAlbum(Album|null $album): self
    {
        $this->album = $album;
        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

}
