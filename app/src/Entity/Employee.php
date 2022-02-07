<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 */
class Employee
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
    private $work_days = [];

    /**
     * @ORM\ManyToOne(targetEntity=Schedule::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_schedule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkDays(): ?array
    {
        return $this->work_days;
    }

    public function setWorkDays(array $work_days): self
    {
        $this->work_days = $work_days;

        return $this;
    }

    public function getIdSchedule(): ?schedule
    {
        return $this->id_schedule;
    }

    public function setIdSchedule(?schedule $id_schedule): self
    {
        $this->id_schedule = $id_schedule;

        return $this;
    }
}
