<?php

namespace App\Entity;

use App\Repository\CursusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursusRepository::class)]
class Cursus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // #[ORM\Column]
    // private ?int $price = null; // price in cents

    #[ORM\ManyToOne(inversedBy: 'cursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(mappedBy: 'cursus', targetEntity: Lesson::class, orphanRemoval: true)]
    private Collection $lessons;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'cursus')]
    private Collection $orderItems;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'cursus')]
    private Collection $certifications;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->certifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // public function getPrice(): ?int
    // {
    //     return $this->price;
    // }

   
    // public function setPrice(int $price): static
    // {
    //     $this->price = $price;
    //     return $this;
    // }

    public function getDynamicPrice(): int
    {
        $lessons = $this->getLessons();
        $total = 0;

        foreach ($lessons as $lesson) {
            $total += $lesson->getPrice();
        }

        // no discount for 1 lesson, 2€ discount per lesson starting from 2 lessons
        if (count($lessons) <= 1) {
            return $total;
        }

        // discount of 2€ per lesson, but max 10€ (so max 5 lessons)
        $discountPerLesson = 200;
        $discount = count($lessons) * $discountPerLesson;

        return max(0, $total - $discount);
    }



    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCursus($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCursus() === $this) {
                $lesson->setCursus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setCursus($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getCursus() === $this) {
                $orderItem->setCursus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certification>
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setCursus($this);
        }

        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            // set the owning side to null (unless already changed)
            if ($certification->getCursus() === $this) {
                $certification->setCursus(null);
            }
        }

        return $this;
    }

    // =========================
        // methode to get lessons ordered by id
        // =========================
    public function getLessonsOrdered(): array
    {
        $lessons = $this->lessons->toArray();

        usort($lessons, function ($a, $b) {
            $posA = $a->getPosition() ?: 9999;
            $posB = $b->getPosition() ?: 9999;

            return $posA <=> $posB;
        });

        return $lessons;
    }



 
    #=========== __toString to enable a select who associate cursus to a lesson ==============
     public function __toString(): string
    {
        return trim($this->name ?: 'Cursus #' . $this->id);
    }


}