<?php

namespace App\Entity;


use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Order;
use App\Entity\LessonValidation;
use App\Entity\Certification;
use App\Entity\EmailVerificationToken;
use App\Entity\Traits\Timestampable;
use App\Entity\Traits\Blameable;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable;
    use Blameable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 191, unique: true)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    // #[ORM\Column]
    // private bool $isActive = true;

     
    // =========================
    // RELATIONS
    // =========================
    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @var Collection<int, LessonValidation>
     */
    #[ORM\OneToMany(targetEntity: LessonValidation::class, mappedBy: 'user')]
    private Collection $lessonValidations;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user')]
    private Collection $certifications;

    /**
     * @var Collection<int, EmailVerificationToken>
     */
    #[ORM\OneToMany(mappedBy: 'user' ,targetEntity: EmailVerificationToken::class, cascade: ['persist', 'remove'], orphanRemoval: true)] 
    private Collection $emailVerificationTokens;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->lessonValidations = new ArrayCollection();
        $this->certifications = new ArrayCollection();
        $this->emailVerificationTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // nothing to do here
    }

    /**
     * Ensure the session doesn't contain password hash
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    // public function isActive(): bool
    // {
    //     return $this->isActive;
    // }

    // public function setIsActive(bool $isActive): static
    // {
    //     $this->isActive = $isActive;
    //     return $this;
    // }
     
    // =========================
    // TIMESTAMPABLE GETTERS/SETTERS
    // =========================
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    // =========================
    // BLAMEABLE GETTERS/SETTERS
    // =========================
    public function getCreatedBy(): ?User { return $this->createdBy; }
    public function setCreatedBy(?User $user): self { $this->createdBy = $user; return $this; }

    public function getUpdatedBy(): ?User { return $this->updatedBy; }
    public function setUpdatedBy(?User $user): self { $this->updatedBy = $user; return $this; }

    // =========================
    // ORDERS
    // =========================
    public function getOrders(): Collection
    {
        return $this->orders;
    }

        public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }
        return $this;
    }


    // =========================
    // LESSON VALIDATIONS
    // =========================
    public function getLessonValidations(): Collection
    {
        return $this->lessonValidations;
    }

        public function addLessonValidation(LessonValidation $validation): self
    {
        if (!$this->lessonValidations->contains($validation)) {
            $this->lessonValidations->add($validation);
            $validation->setUser($this);
        }
        return $this;
    }


    // =========================
    // CERTIFICATIONS
    // =========================
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

        public function addCertification(Certification $cert): self
    {
        if (!$this->certifications->contains($cert)) {
            $this->certifications->add($cert);
            $cert->setUser($this);
        }
        return $this;
    }


    // =========================
    // EMAIL VERIFICATION TOKENS (MULTIPLE)
    // =========================
    public function getEmailVerificationTokens(): Collection
    {
        return $this->emailVerificationTokens;
    }

    public function addEmailVerificationToken(EmailVerificationToken $token): static
    {
        if (!$this->emailVerificationTokens->contains($token)) {
            $this->emailVerificationTokens->add($token);
            $token->setUser($this);
        }

        return $this;
    }

    public function removeEmailVerificationToken(EmailVerificationToken $token): static
    {
        if ($this->emailVerificationTokens->removeElement($token)) {
            if ($token->getUser() === $this) {
                // relation supprimée côté collection uniquement
            }
        }

        return $this;
    }

        // =========================
        // view lessons already purchased by the user
        // =========================
        public function userHasPurchasedLesson(Lesson $lesson): bool
    {
        foreach ($this->getOrders() as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getLesson() && $item->getLesson()->getId() === $lesson->getId()) {
                    return true;
                }
            }
        }
        return false;
    }
         // =========================
        // view courses already purchased by the user
        // =========================
       public function userHasPurchasedCursus(Cursus $cursus): bool
    {
        foreach ($this->getOrders() as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getCursus() && $item->getCursus()->getId() === $cursus->getId()) {
                    return true;
                }
            }
        }
        return false;
    }
      

      // =========================
        // count validated lessons for a given cursus
        // =========================
    public function countUserValidatedLessonsForCursus(Cursus $cursus): int
    {
        return $cursus->getLessons()->filter(function ($lesson) {
            foreach ($this->lessonValidations as $validation) {
                if ($validation->getLesson()->getId() === $lesson->getId()) {
                    return true;
                }
            }
            return false;
        })->count();
    }

      
     // =========================
        // get next lesson to study for a given cursus
        // =========================
        public function findNextLessonToStudy(Cursus $cursus): ?Lesson
    {
        $validatedLessonIds = [];

        foreach ($this->lessonValidations as $validation) {
            $validatedLessonIds[] = $validation->getLesson()->getId();
        }

        foreach ($cursus->getLessons() as $lesson) {
            if (!in_array($lesson->getId(), $validatedLessonIds)) {
                return $lesson; // première leçon non validée
            }
        }

        return null; // tout est validé
    }

    // =========================
        // check if user has certification for a given cursus
        // =========================
    public function userHasCertificationForCursus(Cursus $cursus): bool
    {
        foreach ($this->certifications as $certification) {
            if ($certification->getCursus()->getId() === $cursus->getId()) {
                return true;
            }
        }
        return false;
    }
    
     // =========================
        // get all cursus purchased by the user
        // =========================
    public function getPurchasedCursus(): array
    {
        $courses = [];

        foreach ($this->orders as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getCursus()) {
                    $courses[] = $item->getCursus();
                }
            }
        }

        return array_unique($courses, SORT_REGULAR);
    }

    // =========================
        // get all lessons purchased by the user
        // =========================
    public function getPurchasedLessons(): array
    {
        $lessons = [];

        foreach ($this->orders as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getLesson()) {
                    $lessons[] = $item->getLesson();
                }
            }
        }

        return array_unique($lessons, SORT_REGULAR);
    }

     // =========================
        // calculate remaining price to pay for a given cursus (taking into account already purchased lessons)
        // =========================
    public function calculateRemainingPriceForCursus(Cursus $cursus): float
    {
        $remaining = $cursus->getDynamicPrice();

        foreach ($this->getOrders() as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getLesson() && $item->getLesson()->getCursus() === $cursus) {
                    $remaining -= $item->getLesson()->getPrice();
                }
            }
        }

        return max(0, $remaining);
    }

        public function hasAccessToLesson(Lesson $lesson): bool
    {
        // if user has already purchased the lesson, they have access
        if ($this->userHasPurchasedLesson($lesson)) {
            return true;
        }

        // if user has already purchased the cursus, they have access to all its lessons
        if ($this->userHasPurchasedCursus($lesson->getCursus())) {
            return true;
        }

        return false;
    }


    public function __toString(): string
    {
        return $this->email ?? '';
    }

}