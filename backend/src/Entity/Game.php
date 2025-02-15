<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GameRepository;
use App\Entity\Traits\Timestampable;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource()]
class Game
{
    public const MAX_LENGTH_DESCRIPTION = 65535;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['usergame:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['usergame:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: self::MAX_LENGTH_DESCRIPTION)]
    #[Groups(['usergame:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['usergame:read'])]
    private ?string $cover = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['usergame:read'])]
    private ?string $developer = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['usergame:read'])]
    private ?string $publisher = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Date]
    #[Groups(['usergame:read'])]
    private ?\DateTimeInterface $releasedAt = null;

    /**
     * @var Collection<int, Platform>
     */
    #[ORM\ManyToMany(targetEntity: Platform::class, inversedBy: 'games')]
    #[Assert\Valid]
    #[Groups(['usergame:read'])]
    private Collection $platforms;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: genre::class, inversedBy: 'games')]
    #[Assert\Valid]
    #[Groups(['usergame:read'])]
    private Collection $genres;

    /**
     * @var Collection<int, Mode>
     */
    #[ORM\ManyToMany(targetEntity: Mode::class, inversedBy: 'games')]
    #[Assert\Valid]
    #[Groups(['usergame:read'])]
    private Collection $modes;

    /**
     * @var Collection<int, Theme>
     */
    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'games')]
    #[Assert\Valid]
    #[Groups(['usergame:read'])]
    private Collection $themes;

    /**
     * @var Collection<int, UserGame>
     */
    #[ORM\OneToMany(targetEntity: UserGame::class, mappedBy: 'game', orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $collectors;

    #[ORM\Column]
    #[Groups(['usergame:read'])]
    private ?int $igdbId = null;

    /**
     * @var Collection<int, GameClient>
     */
    #[ORM\OneToMany(targetEntity: GameClient::class, mappedBy: 'game')]
    private Collection $clients;

    use Timestampable;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->genres = new ArrayCollection();
        $this->modes = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->collectors = new ArrayCollection();
        $this->platforms = new ArrayCollection();
        $this->clients = new ArrayCollection();
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
    
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getDeveloper(): ?string
    {
        return $this->developer;
    }

    public function setDeveloper(?string $developer): static
    {
        $this->developer = $developer;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(?string $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeInterface
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(?\DateTimeInterface $releasedAt): static
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Platform>
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function addPlatform(Platform $platform): static
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms->add($platform);
        }

        return $this;
    }

    public function removePlatform(Platform $platform): static
    {
        $this->platforms->removeElement($platform);

        return $this;
    }

    /**
     * @return Collection<int, genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(genre $genre): static
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection<int, Mode>
     */
    public function getModes(): Collection
    {
        return $this->modes;
    }

    public function addMode(Mode $mode): static
    {
        if (!$this->modes->contains($mode)) {
            $this->modes->add($mode);
        }

        return $this;
    }

    public function removeMode(Mode $mode): static
    {
        $this->modes->removeElement($mode);

        return $this;
    }

        /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        $this->themes->removeElement($theme);

        return $this;
    }

    /**
     * @return Collection<int, UserGame>
     */
    public function getCollectors(): Collection
    {
        return $this->collectors;
    }

    public function addCollector(UserGame $collector): static
    {
        if (!$this->collectors->contains($collector)) {
            $this->collectors->add($collector);
            $collector->setGame($this);
        }

        return $this;
    }

    public function removeCollector(UserGame $collector): static
    {
        if ($this->collectors->removeElement($collector)) {
            // set the owning side to null (unless already changed)
            if ($collector->getGame() === $this) {
                $collector->setGame(null);
            }
        }

        return $this;
    }

    public function getIgdbId(): ?int
    {
        return $this->igdbId;
    }

    public function setIgdbId(int $igdbId): static
    {
        $this->igdbId = $igdbId;

        return $this;
    }

    /**
     * @return Collection<int, GameClient>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(GameClient $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setGame($this);
        }

        return $this;
    }

    public function removeClient(GameClient $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getGame() === $this) {
                $client->setGame(null);
            }
        }

        return $this;
    }
}
