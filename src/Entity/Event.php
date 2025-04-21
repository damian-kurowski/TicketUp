<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 255)]
    private ?string $locationName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locationAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: TicketType::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $ticketTypes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->ticketTypes = new ArrayCollection();
    }


    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function setStartDate(\DateTimeImmutable $startDate): static { $this->startDate = $startDate; return $this; }
    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(?\DateTimeImmutable $endDate): static { $this->endDate = $endDate; return $this; }
    public function getLocationName(): ?string { return $this->locationName; }
    public function setLocationName(string $locationName): static { $this->locationName = $locationName; return $this; }
    public function getLocationAddress(): ?string { return $this->locationAddress; }
    public function setLocationAddress(?string $locationAddress): static { $this->locationAddress = $locationAddress; return $this; }
    public function getImageFilename(): ?string { return $this->imageFilename; }
    public function setImageFilename(?string $imageFilename): static { $this->imageFilename = $imageFilename; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    /**
     * @return Collection<int, TicketType>
     */
    public function getTicketTypes(): Collection
    {
        return $this->ticketTypes;
    }

    public function addTicketType(TicketType $ticketType): static
    {
        if (!$this->ticketTypes->contains($ticketType)) {
            $this->ticketTypes->add($ticketType);
            $ticketType->setEvent($this);
        }
        return $this;
    }

    public function removeTicketType(TicketType $ticketType): static
    {
        if ($this->ticketTypes->removeElement($ticketType)) {
            if ($ticketType->getEvent() === $this) {
                $ticketType->setEvent(null);
            }
        }
        return $this;
    }
}