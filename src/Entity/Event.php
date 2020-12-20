<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="text")
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(name="email", type="text")
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(name="phone", type="string", length=25)
     * @Assert\NotBlank
     */
    private $phone;

    /**
     * @ORM\Column(name="start", type="datetime")
     * @Assert\NotBlank
     */
    private $start;

    /**
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end = null;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank
     */
    private $title;

    //Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    //Setters

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
