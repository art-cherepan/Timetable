<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScheduleRepository::class)
 */
class Schedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $time_ranges = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeRanges(): ?array
    {
        return $this->time_ranges;
    }

    public function setTimeRanges(array $time_ranges): self
    {
        $this->time_ranges = $time_ranges;

        return $this;
    }
}
