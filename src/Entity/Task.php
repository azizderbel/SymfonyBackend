<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("extract")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("extract")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("extract")
     */
    private $responsible;

    /**
     * @ORM\Column(type="boolean")
     *  @Groups("extract")
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=Todolist::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $todolist;

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

    public function getResponsible(): ?string
    {
        return $this->responsible;
    }

    public function setResponsible(string $responsible): self
    {
        $this->responsible = $responsible;

        return $this;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getTodolist(): ?Todolist
    {
        return $this->todolist;
    }

    public function setTodolist(?Todolist $todolist): self
    {
        $this->todolist = $todolist;

        return $this;
    }
}
