<?php

namespace App\Entity;

use App\Repository\AchatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
class Achat
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name:'idAchat')]
    private ?int $idAchat = null;

    #[ORM\Column]
    private int $quantite ;

    #[ORM\Column]
    private float $prixAchat;

    
    #[ORM\ManyToOne(inversedBy: 'achats', cascade:["persist"])]
    #[ORM\JoinColumn(nullable: true, name: 'idCommande', referencedColumnName: 'idCommande')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'achats', cascade:["persist"])]
    #[ORM\JoinColumn(nullable: true, name: 'idProduit', referencedColumnName: 'idProduit')]
    private ?Produit $product = null;

   
   

    public function __construct($product) {
        $this->quantite = 1;
        $this->prixAchat = $product->getPrix();
        $this->product = $product;
    
        
    }

    public function update($quantity) {
         $this->quantite = $quantity;  
 
    }

   
    
    public function getIdAchat(): ?int
    {
        return $this->idAchat;
    }

    public function setIdAchat(int $idAchat): self
    {
        $this->idAchat = $idAchat;

        return $this;
    }

    public function getProduct(): ?Produit
    {
        return $this->product;
    }

    public function setProduct(?Produit $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixAchat(): ?float
    {        
        return $this->prixAchat;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function addProduct(Produit $product): self
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
            $product->setAchat($this);
        }

        return $this;
    }

    public function removeProduct(Produit $product): self
    {
        if ($this->product->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getAchat() === $this) {
                $product->setAchat(null);
            }
        }

        return $this;
    }

   

    
}
