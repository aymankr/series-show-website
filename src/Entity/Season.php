<?php

namespace App\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Season
 *
 * @ORM\Table(name="season", indexes={@ORM\Index(name="IDX_F0E45BA95278319C", columns={"series_id"})})
 * @ORM\Entity
 */
class Season
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
     * @var int
     *
     * @ORM\Column(name="number", type="integer", nullable=false)
     */
    private $number;

    /**
     * @var \Series
     *
     * @ORM\ManyToOne(targetEntity="Series", inversedBy="seasons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="series_id", referencedColumnName="id")
     * })
     */
    private $series;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="season")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="season_id", referencedColumnName="id")
     * })
     */
    private $episodes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return Collection|Episode[] ordered by number
     */
    public function getEpisodesOrdered(): Collection
    {
        $sort = new Criteria(null, ['number' => Criteria::ASC]);
        return $this->episodes->matching($sort);
    }

    /**
     * @return int the number of episode seen in this season by the given user.
     */
    public function getNumberOfSeenEpisode(User $user): int
    {
        $counter = 0;
        foreach ($this->episodes as $episode) {
            if ($episode->getUser()->contains($user)) {
                $counter++;
            }
        }

        return $counter;
    }
}
