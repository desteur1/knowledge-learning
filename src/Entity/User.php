<?php

namespace App\Entity;

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

    // #[ORM\Column]
    // private bool $isActive = true;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

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

    #[ORM\Column]
    private bool $isVerified = false;

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

    // public function isActive(): bool
    // {
    //     return $this->isActive;
    // }

    // public function setIsActive(bool $isActive): static
    // {
    //     $this->isActive = $isActive;
    //     return $this;
    // }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    // =========================
    // ORDERS
    // =========================
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    // =========================
    // LESSON VALIDATIONS
    // =========================
    public function getLessonValidations(): Collection
    {
        return $this->lessonValidations;
    }

    // =========================
    // CERTIFICATIONS
    // =========================
    public function getCertifications(): Collection
    {
        return $this->certifications;
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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

   

}