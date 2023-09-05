<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idClient = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'nom')]
    #[Assert\Length(min: 2, minMessage: "Le nom doit contenir {{ limit }}caractères minimum")]
    #[Assert\Length(max: 30, maxMessage: "Le nom doit contenir {{ limit }}caractères minimum")]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom')]
    #[Assert\Length(min: 2, minMessage: "Le prenom doit contenir {{ limit }}caractères minimum")]
    #[Assert\Length(max: 30, maxMessage: "Le prenom doit contenir {{ limit }}caractères minimum")]
    private ?string $prenom = null;

    #[ORM\Column(name: 'adresse')]
    #[Assert\Length(min: 5, minMessage: "L'adresse doit contenir {{ limit }}caractères minimum")]
    #[Assert\Length(max: 100, maxMessage: "L'adresse doit contenir {{ limit }}caractères minimum")]
    private ?string $adresse = null;

    #[ORM\Column(name: 'ville')]
    #[Assert\Length(min: 3, minMessage: "L'adresse doit contenir {{ limit }}caractères minimum")]
    #[Assert\Length(max: 30, maxMessage: "L'adresse doit contenir {{ limit }}caractères minimum")]
    private ?string $ville = null;

    #[ORM\Column(name: 'codePostal')]
    //  '/^([ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9])*$/'
    #[Assert\Regex(pattern: "/^[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTV-Z][ -]?\d[ABCEGHJ-NPRSTV-Z]\d$/i", message: "Your postal code must respect the canadian form")]
    private ?string $codePostal = null;

    #[ORM\Column(name: 'province')]
    // /^(?:AB|BC|MB|N[BLTSU]|ON|PE|QC|SK|YT)*$/
   // #[Assert\Regex(pattern: "/^(?:AB|BC|MB|N[BLTSU]|ON|PE|QC|SK|YT)*$/", message: "Your province must be a candian province with two letters")]
    private ?string $province = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: "/^[0-9]{10}$/", message: "Votre téléphone doit contenir 10 chiffres")]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commande::class, orphanRemoval: true)]
    #[ORM\OrderBy(["idCommande" => "DESC"])]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->idClient;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }  
    
    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }
    
    public function getcodePostal(): ?string
    {
        return $this->codePostal;
    }   
    
    public function setcodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }  
    
    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }   
    
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
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
    public function getUserIdentifier(): string
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
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setClient($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getClient() === $this) {
                $commande->setClient(null);
            }
        }

        return $this;
    }
}
