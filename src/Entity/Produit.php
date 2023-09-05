<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produits')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name:'idProduit')]
    private ?int $idProduit = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column (name:'quantiteEnStock')]
    private ?int $quantiteEnStock = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, name:'imagePath')]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "produits", cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idCategorie', referencedColumnName: 'idCategorie')]
    private $categorie;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Achat::class)]
    private Collection $achats;

    public function __construct()
    {
        $this->achats = new ArrayCollection();
    }

  
    

    public function getIdCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }


    public function getQuantiteEnStock(): ?int
    {
        return $this->quantiteEnStock;
    }

   
    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

   
    public function setQuantiteEnStock(int $achatQuantite): self
    {
    
        $this->quantiteEnStock = $this->quantiteEnStock - $achatQuantite;
        return $this;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getAchats(): Collection
    {
        return $this->achats;
    }

    public function addAchat(Achat $achat): self
    {
        if (!$this->achats->contains($achat)) {
            $this->achats->add($achat);
            $achat->setProduct($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): self
    {
        if ($this->achats->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getProduct() === $this) {
                $achat->setProduct(null);
            }
        }

        return $this;
    }

    public function checkIfBackOrder(): ?bool{
        if ($this->getQuantiteEnStock() < 0) {
            return true;
        }
        else
        return false;        
    }

}
