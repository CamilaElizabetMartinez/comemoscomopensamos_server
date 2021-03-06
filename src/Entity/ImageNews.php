<?php

namespace App\Entity;

use App\Repository\ImageNewsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageNewsRepository::class)
 */
class ImageNews
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $img_file;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="imageNews")
     */
    private $news;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(?News $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getImgFile(): ?string
    {
        return $this->img_file;
    }

    public function setImgFile(string $img_file): self
    {
        $this->img_file = $img_file;

        return $this;
    }
}
