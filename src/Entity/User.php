<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @method string getUserIdentifier()
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $lastname;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isMale;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $birthDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $images = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $background;


    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private Collection $posts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $lastLogin;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $favorites;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $likes;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $friends;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $friendRequests;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $visitors;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $visitorsLastChecked;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="user", orphanRemoval=true)
     */
    private $messages;

    public function __construct()
    {
        $this->roles = array('ROLE_USER');
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER

        return array_unique($roles);
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getIsMale(): ?bool
    {
        return $this->isMale;
    }

    public function setIsMale(bool $isMale): void
    {
        $this->isMale = $isMale;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function addImages(array $images): void
    {
        if (!in_array($images, $this->images)) {
            $this->images = array_merge($this->images, $images);
        }
    }

    public function removeImage(int $id): void
    {
        array_splice($this->images, $id, 1);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(int $id): void
    {
        $this->avatar = $this->images[$id];
    }

    public function removeAvatar(): void
    {
        $this->avatar = null;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(int $id): void
    {
        $this->background = $this->images[$id];
    }

    public function removeBackground(): void
    {
        $this->background = null;
    }

    public function moveImageUp($id): void
    {
        $out = array_splice($this->images, $id, 1);
        array_splice($this->images, $id - 1, 0, $out);
    }

    public function moveImageDown($id): void
    {
        $out = array_splice($this->images, $id, 1);
        array_splice($this->images, $id + 1, 0, $out);
    }


    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): void
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }
    }

    public function removePost(Post $post): void
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }
    }

    // LIKES
    public function getLikes(): ?array
    {
        return $this->likes;
    }

    public function addLike(int $id): void
    {
        if (!in_array($id, $this->likes)) {
            $this->likes[] = $id;
        }
    }

    public function removeLike(int $id): void
    {
        if (in_array($id, $this->likes)) {
            unset($this->likes[array_search($id, $this->likes)]);
        }
    }

    public function getLastLogin(): ?int
    {
        return $this->lastLogin;
    }

    public function setLastLogin(int $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    // FAVORITES
    public function getFavorites(): ?array
    {
        return $this->favorites;
    }

    public function addFavorite(int $id): void
    {
        if (!in_array($id, $this->favorites)) {
            $this->favorites[] = $id;
        }
    }

    public function removeFavorite(int $id): void
    {
        if (in_array($id, $this->favorites)) {
            unset($this->favorites[array_search($id, $this->favorites)]);
        }
    }

    // FRIENDS
    public function getFriends(): ?array
    {
        return $this->friends;
    }

    public function addFriend(int $id): void
    {
        if (!in_array($id, $this->friends)) {
            $this->friends[] = $id;
        }
    }

    public function removeFriend(int $id): void
    {
        if (in_array($id, $this->friends)) {
            unset($this->friends[array_search($id, $this->friends)]);
        }
    }

    // FRIEND REQUESTS
    public function getFriendRequests(): ?array
    {
        return $this->friendRequests;
    }

    public function addFriendRequest(int $id): void
    {
        if (!in_array($id, $this->friendRequests)) {
            $this->friendRequests[] = $id;
        }
    }

    public function removeFriendRequest(int $id): void
    {
        if (in_array($id, $this->friendRequests)) {
            unset($this->friendRequests[array_search($id, $this->friendRequests)]);
        }
    }

    // VISITORS
    public function getVisitors(): ?array
    {
        return $this->visitors;
    }

    public function addVisitor(?array $visitor): void
    {
        if ($this->id != key($visitor)) {
            if (!in_array($visitor, $this->visitors)) {
                if (is_numeric($visitor)) {
                    $visitor = (int)$visitor;
                }
                if (is_array($visitor)) {
                    $keys = array_keys($this->visitors);
                    array_push($keys, key($visitor));
                    $values = array_values($this->visitors);
                    array_push($values, $visitor[key($visitor)]);
                    $this->visitors = array_combine($keys, $values);
                } else {
                    array_push($this->visitors, $visitor);
                }
            }
        }
    }

    public function getVisitorsLastChecked(): ?int
    {
        return $this->visitorsLastChecked;
    }

    public function setVisitorsLastChecked(?int $visitorsLastChecked): void
    {
        $this->visitorsLastChecked = $visitorsLastChecked;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }
}
