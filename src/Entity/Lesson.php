<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoPath = null;

    #[ORM\Column]
    private ?int $price = null; // price in cents

    #[ORM\ManyToOne (inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cursus $cursus = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'lesson')]
    private Collection $orderItems;

    /**
     * @var Collection<int, LessonValidation>
     */
    #[ORM\OneToMany(targetEntity: LessonValidation::class, mappedBy: 'lesson')]
    private Collection $lessonValidations;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->lessonValidations = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function setVideoPath(?string $videoPath): static
    {
        $this->videoPath = $videoPath;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus): static
    {
         // Avoid infinite loop
        if ($this->cursus === $cursus) {
            return $this;
        }
       // Remove from old cursus
        if ($this->cursus !== null) {
            $this->cursus->getLessons()->removeElement($this);
        }

        $this->cursus = $cursus;
       // Ensure the bidirectional relationship is maintained
        if ($cursus !== null && !$cursus->getLessons()->contains($this)) {
            $cursus->getLessons()->add($this);
        }

        return $this;
    }

     public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
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
            $orderItem->setLesson($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getLesson() === $this) {
                $orderItem->setLesson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LessonValidation>
     */
    public function getLessonValidations(): Collection
    {
        return $this->lessonValidations;
    }

    public function addLessonValidation(LessonValidation $lessonValidation): static
    {
        if (!$this->lessonValidations->contains($lessonValidation)) {
            $this->lessonValidations->add($lessonValidation);
            $lessonValidation->setLesson($this);
        }

        return $this;
    }

    public function removeLessonValidation(LessonValidation $lessonValidation): static
    {
        if ($this->lessonValidations->removeElement($lessonValidation)) {
            // set the owning side to null (unless already changed)
            if ($lessonValidation->getLesson() === $this) {
                $lessonValidation->setLesson(null);
            }
        }

        return $this;
    }

    // =========================
        // methods to manage the bidirectional relationship with Cursus
        // =========================
     public function getNextLesson(): ?Lesson
    {
        $lessons = $this->getCursus()->getLessonsOrdered();

        foreach ($lessons as $index => $lesson) {
            if ($lesson->getId() === $this->getId()) {
                return $lessons[$index + 1] ?? null;
            }
        }

        return null;
    }

    public function getPreviousLesson(): ?Lesson
    {
        $lessons = $this->getCursus()->getLessonsOrdered();

        foreach ($lessons as $index => $lesson) {
            if ($lesson->getId() === $this->getId()) {
                return $lessons[$index - 1] ?? null;
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
    


}
