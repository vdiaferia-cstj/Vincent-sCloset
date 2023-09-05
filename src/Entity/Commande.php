<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Core\Constantes;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{  
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name:'idCommande')]
    private ?int $idCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column]
    private ?float $tauxTPS = null;

    #[ORM\Column]
    private ?float $tauxTVQ = null;

    #[ORM\Column]
    private ?float $fraisLivraison = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeIntent = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Achat::class, orphanRemoval: true, cascade:["persist"])]
    private Collection $achats;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'commandes', cascade:["persist"])]
    #[ORM\JoinColumn(nullable: false, name: 'id_client', referencedColumnName: 'id_client')]
    private ?Client $client = null;

    public function __construct($panier, $paymentIntent, $client)
    {
        $this->tauxTPS = Constantes::TPS;
        $this->tauxTVQ = Constantes::TVQ;
        $this->etat = "In preparation";
        $this->fraisLivraison = Constantes::FRAIS_LIVRAISON;
        $this->stripeIntent = $paymentIntent;
        $this->dateCommande = date_create();
        $this->achats = new ArrayCollection();
        foreach ($panier->getPanier() as $achat) {
            $this->addAchat($achat);
            $achat->setCommande($this);
           
        }       
        $this->client = $client; 
    }
    public function getSousTotal(): ?float{
        $sousTotal = 0;

        foreach ($this->achats as $achat) {
            $sousTotal += (round($achat->getPrixAchat() * $achat->getQuantite(),2));        
         }

         return $sousTotal;
    }


    public function getTotal(): ?float
    {
        $total = 0;
        $tps = 0;
        $tvq = 0;
        
        foreach ($this->achats as $achat) {
           $total += (round($achat->getPrixAchat() * $achat->getQuantite(),2));        
        }
        $tps = round($total * $this->tauxTPS, 2);
        $tvq = round($total * $this->tauxTVQ,2);
        $fraisLivraison = $this->fraisLivraison;
        $total += ($tps + $tvq + $fraisLivraison );

        return $total;
    }

    public function getTPS(): ?float {
        $total = $this->getSousTotal();
        $tps = round($total * $this->tauxTPS, 2);
        return $tps;
    }

    public function getTVQ(): ?float {
        $total = $this->getSousTotal();
        $tvq = round($total * $this->tauxTVQ, 2);
        return $tvq;
    }


    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function setIdCommande(int $idCommande): self
    {
        $this->idCommande = $idCommande;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function getDateCommandeString(): ?string
    {
        return $this->dateCommande->format('Y-m-d H:i:s');
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getTauxTPS(): ?float
    {
        return $this->tauxTPS;
    }

    public function setTauxTPS(float $tauxTPS): self
    {
        $this->tauxTPS = $tauxTPS;

        return $this;
    }

    public function getTauxTVQ(): ?float
    {
        return $this->tauxTVQ;
    }

    public function setTauxTVQ(float $tauxTVQ): self
    {
        $this->tauxTVQ = $tauxTVQ;

        return $this;
    }

    public function getFraisLivraison(): ?float
    {
        return $this->fraisLivraison;
    }

    public function setFraisLivraison(float $fraisLivraison): self
    {
        $this->fraisLivraison = $fraisLivraison;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getStripeIntent(): ?string
    {
        return $this->stripeIntent;
    }

    public function setStripeIntent(string $stripeIntent): self
    {
        $this->stripeIntent = $stripeIntent;

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
            $achat->setCommande($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): self
    {
        if ($this->achats->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getCommande() === $this) {
                $achat->setCommande(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
