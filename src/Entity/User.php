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
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMale;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private $images = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $background;


    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $favorites = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $likes = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $friends = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $friendRequests = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $visitors = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $visitorsLastChecked;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->roles = array('ROLE_USER');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getIsMale(): ?bool
    {
        return $this->isMale;
    }

    public function setIsMale(bool $isMale): self
    {
        $this->isMale = $isMale;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function addImages(array $images): self
    {
        if (!in_array($images, $this->images)) {
            $this->images = array_merge($this->images, $images);
        }

        return $this;
    }

    public function removeImage(int $id): self
    {
        array_splice($this->images, $id, 1);

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(int $id): self
    {
        $this->avatar = $this->images[$id];

        return $this;
    }

    public function removeAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(int $id): self
    {
        $this->background = $this->images[$id];

        return $this;
    }

    public function removeBackground(): self
    {
        $this->background = null;

        return $this;
    }

    public function moveImageUp($id): self
    {
        $out = array_splice($this->images, $id, 1);
        array_splice($this->images, $id - 1, 0, $out);

        return $this;
    }

    public function moveImageDown($id): self
    {
        $out = array_splice($this->images, $id, 1);
        array_splice($this->images, $id + 1, 0, $out);

        return $this;
    }


    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    // LIKES
    public function getLikes(): ?array
    {
        return $this->likes;
    }

    public function addLike(int $id): self
    {
        if (!in_array($id, $this->likes)) {
            $this->likes[] = $id;
        }

        return $this;
    }

    public function removeLike(int $id): self
    {
        if (in_array($id, $this->likes)) {
            unset($this->likes[array_search($id, $this->likes)]);
        }

        return $this;
    }

    public function getLastLogin(): ?int
    {
        return $this->lastLogin;
    }

    public function setLastLogin(int $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    // FAVORITES
    public function getFavorites(): ?array
    {
        return $this->favorites;
    }

    public function addFavorite(int $id): self
    {
        if (!in_array($id, $this->favorites)) {
            $this->favorites[] = $id;
        }

        return $this;
    }

    public function removeFavorite(int $id): self
    {
        if (in_array($id, $this->favorites)) {
            unset($this->favorites[array_search($id, $this->favorites)]);
        }

        return $this;
    }

    // FRIENDS
    public function getFriends(): ?array
    {
        return $this->friends;
    }

    public function addFriend(int $id): self
    {
        if (!in_array($id, $this->friends)) {
            $this->friends[] = $id;
        }

        return $this;
    }

    public function removeFriend(int $id): self
    {
        if (in_array($id, $this->friends)) {
            unset($this->friends[array_search($id, $this->friends)]);
        }

        return $this;
    }

    // FRIEND REQUESTS
    public function getFriendRequests(): ?array
    {
        return $this->friendRequests;
    }

    public function addFriendRequest(int $id): self
    {
        if (!in_array($id, $this->friendRequests)) {
            $this->friendRequests[] = $id;
        }

        return $this;
    }

    public function removeFriendRequest(int $id): self
    {
        if (in_array($id, $this->friendRequests)) {
            unset($this->friendRequests[array_search($id, $this->friendRequests)]);
        }

        return $this;
    }

    // VISITORS
    public function getVisitors(): ?array
    {
        return $this->visitors;
    }

    public function addVisitor(?array $visitor): self
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

        return $this;
    }

    public function getVisitorsLastChecked(): ?int
    {
        return $this->visitorsLastChecked;
    }

    public function setVisitorsLastChecked(?int $visitorsLastChecked): self
    {
        $this->visitorsLastChecked = $visitorsLastChecked;

        return $this;
    }
}
