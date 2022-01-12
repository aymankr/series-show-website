<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Series
 *
 * @ORM\Table(name="series", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_3A10012D85489131", columns={"imdb"})})
 * @ORM\Entity(repositoryClass="App\Repository\SeriesRepository")
 */
class Series
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128, nullable=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="plot", type="text", length=0, nullable=true, options={"default"="NULL"})
     */
    private $plot = 'NULL';

    /**
     * @var string
     *
     * @ORM\Column(name="imdb", type="string", length=128, nullable=false)
     */
    private $imdb;

    /**
     * @var string|null
     *
     * @ORM\Column(name="poster", type="blob", length=0, nullable=true, options={"default"="NULL"})
     */
    private $poster = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="director", type="string", length=128, nullable=true, options={"default"="NULL"})
     */
    private $director = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="youtube_trailer", type="string", length=128, nullable=true, options={"default"="NULL"})
     */
    private $youtubeTrailer = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="awards", type="text", length=0, nullable=true, options={"default"="NULL"})
     */
    private $awards = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="year_start", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $yearStart = NULL;

    /**
     * @var int|null
     *
     * @ORM\Column(name="year_end", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $yearEnd = NULL;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Actor", mappedBy="series")
     */
    private $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Country", mappedBy="series")
     */
    private $country;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Genre", mappedBy="series")
     */
    private $genre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="series")
     */
    private $user;

    /**
     * @var \Seasons
     *
     * @ORM\OneToMany(targetEntity="Season", mappedBy="series")
     */
    private $seasons;

    /**
     * @var \Rating
     * 
     * @ORM\OneToMany(targetEntity="Rating", mappedBy="series")
     */
    private $ratings;

    /**
     * @var \ExternalRating
     * 
     * @ORM\OneToOne(targetEntity="ExternalRating", mappedBy="series")
     */
    private $externalRating;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actor = new \Doctrine\Common\Collections\ArrayCollection();
        $this->country = new \Doctrine\Common\Collections\ArrayCollection();
        $this->genre = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->seasons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ratings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPlot(): ?string
    {
        return $this->plot;
    }

    public function setPlot(?string $plot): self
    {
        $this->plot = $plot;

        return $this;
    }

    public function getImdb(): ?string
    {
        return $this->imdb;
    }

    public function setImdb(string $imdb): self
    {
        $this->imdb = $imdb;

        return $this;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setPoster($poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getYoutubeTrailer(): ?string
    {
        // return only the id of the link
        $results = NULL;
        preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $this->youtubeTrailer, $results);
        return $results[6];
    }

    public function setYoutubeTrailer(?string $youtubeTrailer): self
    {
        $this->youtubeTrailer = $youtubeTrailer;

        return $this;
    }

    public function getAwards(): ?string
    {
        return $this->awards;
    }

    public function setAwards(?string $awards): self
    {
        $this->awards = $awards;

        return $this;
    }

    public function getYearStart(): ?int
    {
        return $this->yearStart;
    }

    public function setYearStart(?int $yearStart): self
    {
        $this->yearStart = $yearStart;

        return $this;
    }

    public function getYearEnd(): ?int
    {
        return $this->yearEnd;
    }

    public function setYearEnd(?int $yearEnd): self
    {
        $this->yearEnd = $yearEnd;

        return $this;
    }

    /**
     * @return Collection|Actor[]
     */
    public function getActor(): Collection
    {
        return $this->actor;
    }

    public function addActor(Actor $actor): self
    {
        if (!$this->actor->contains($actor)) {
            $this->actor[] = $actor;
            $actor->addSeries($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): self
    {
        if ($this->actor->removeElement($actor)) {
            $actor->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|Country[]
     */
    public function getCountry(): Collection
    {
        return $this->country;
    }

    public function addCountry(Country $country): self
    {
        if (!$this->country->contains($country)) {
            $this->country[] = $country;
            $country->addSeries($this);
        }

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        if ($this->country->removeElement($country)) {
            $country->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenre(): Collection
    {
        return $this->genre;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genre->contains($genre)) {
            $this->genre[] = $genre;
            $genre->addSeries($this);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        if ($this->genre->removeElement($genre)) {
            $genre->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->addSeries($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->removeElement($user)) {
            $user->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    /**
     * @return Collection|Season[] ordered by number
     */
    public function getSeasonsOrdered(): Collection
    {
        $sort = new Criteria(null, ['number' => Criteria::ASC]);
        return $this->seasons->matching($sort);
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setSeries($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getSeries() === $this) {
                $season->setSeries(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    /**
     * @param User the user that possibly rated this serie
     * @return Rating|null
     */
    public function getRatingByUser(User $user): Rating
    {
        foreach ($this->ratings as $rating) {
            if ($rating->getUser() === $user) {
                return $rating;
            }
        }
        return null;
    }

    /**
     * @param User the user that possibly rated this serie
     * @return bool
     */
    public function hasBeenRatedByUser(User $user): bool
    {
        foreach ($this->ratings as $rating) {
            if ($rating->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ExternalRating
     */
    public function getExternalRating()
    {
        return $this->externalRating;
    }

    /**
     * @return ExternalRating
     */
    public function getExternalRatingValue()
    {
        return $this->externalRating->getValue();
    }
}
