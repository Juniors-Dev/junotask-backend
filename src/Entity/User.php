<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use App\State\MeProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enums\User\JobPosition;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(uriTemplate: '/me', provider: MeProvider::class),
        new Post(),
        new Put(),
        new Delete(),
    ]
)]
class User implements \Symfony\Component\Security\Core\User\UserInterface
{
    use Common\TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:read', 'leave:read', 'project:read'])]
    private ?string $name = null;

    #[ORM\Column(enumType: JobPosition::class)]
    private ?JobPosition $jobPosition = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isAdmin = false;

    /** @var Collection<int, Task> */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    /** @var Collection<int, Project> */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'user')]
    private Collection $projects;

    /**
     * @var Collection<int, Leave>
     */
    #[ORM\OneToMany(targetEntity: Leave::class, mappedBy: 'user')]
    private Collection $leave;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->leave = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getJobPosition(): ?JobPosition
    {
        return $this->jobPosition;
    }

    public function setJobPosition(JobPosition $jobPosition): static
    {
        $this->jobPosition = $jobPosition;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Leave>
     */
    public function getLeave(): Collection
    {
        return $this->leave;
    }

    public function addLeave(Leave $leave): static
    {
        if (!$this->leave->contains($leave)) {
            $this->leave->add($leave);
            $leave->setUser($this);
        }

        return $this;
    }


    public function removeLeave(Leave $leave): static
    {
        if ($this->leave->removeElement($leave)) {
            // set the owning side to null (unless already changed)
            if ($leave->getUser() === $this) {
                $leave->setUser(null);
            }
        }

        return $this;
    }

    // Required by UserInterface
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // Security roles for permissions, not job positions
        $roles = ['ROLE_USER'];
        if ($this->isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }
        return $roles;
    }

    public function eraseCredentials(): void
    {
        // Nothing to clear for OAuth
    }
}
