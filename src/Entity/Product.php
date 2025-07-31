<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column]
    private ?int $Price = null;

    /**
     * @var Collection<int, SubCategory>
     */
    #[ORM\ManyToMany(targetEntity: SubCategory::class, inversedBy: 'products')]
    private Collection $SubCategory;

    public function __construct()
    {
        $this->SubCategory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->Price;
    }

    public function setPrice(int $Price): static
    {
        $this->Price = $Price;

        return $this;
    }

    /**
     * @return Collection<int, SubCategory>
     */
    public function getSubCategory(): Collection
    {
        return $this->SubCategory;
    }

    public function addSubCategory(SubCategory $subCategory): static
    {
        if (!$this->SubCategory->contains($subCategory)) {
            $this->SubCategory->add($subCategory);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): static
    {
        $this->SubCategory->removeElement($subCategory);

        return $this;
    }
}
