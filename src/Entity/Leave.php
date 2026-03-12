<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\LeaveRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enums\Leave\Type;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Enums\Leave\LeaveState;
use App\State\LeaveActiveProvider;
use App\State\LeaveProcessor;
use ApiPlatform\Metadata\QueryParameter;

#[ORM\Entity(repositoryClass: LeaveRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(uriTemplate: '/active', provider: LeaveActiveProvider::class),
        new GetCollection(
            uriTemplate: '/bystatus',
            provider: LeaveActiveProvider::class,
            parameters: ['status' => new QueryParameter(required: true)],
        ),
        new Post(processor: LeaveProcessor::class),
        new Put(processor: LeaveProcessor::class),
        new Patch(processor: LeaveProcessor::class),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['leave:read']],
)]
#[ApiFilter(OrderFilter::class, properties: ['startDate', 'endDate'])]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact', 'state' => 'exact'])]
class Leave
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['leave:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'leave')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['leave:read'])]
    private ?User $user = null;

    #[ORM\Column(enumType: Type::class)]
    #[Groups(['leave:read'])]
    private ?Type $type = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['leave:read'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['leave:read'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(enumType: LeaveState::class)]
    #[Groups(['leave:read'])]
    private LeaveState $state = LeaveState::Pending;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['leave:read'])]
    private ?string $comment = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getState(): ?LeaveState
    {
        return $this->state;
    }

    public function setState(LeaveState $state): static
    {
        $this->state = $state;

        return $this;
    }

    #[Groups(['leave:read'])]
    public function getStateLabel(): string
    {
        return $this->state->getLabel();
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function appendComment(string $entry): static
    {
        $this->comment = $this->comment
            ? $this->comment . "\n" . $entry
            : $entry;

        return $this;
    }
}
