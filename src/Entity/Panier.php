<?php

namespace App\Entity;
use App\Core\Constantes;

class Panier
{
    private $panier = [];
    private $sousTotal = 0.0;
    private $total=0.0;
    private $tvq =0.0;
    private $tps = 0.0;
    private $fraisLivraison = Constantes::FRAIS_LIVRAISON;
    
    public function add($product)
    {
        $caseIfExists = $this->alreadyExist($product);
        if ($caseIfExists == -1) {
            //Achat n'existe pas déjà
            $achat = new Achat($product);
            $this->panier[] = $achat;
        } else {
            //Si l'achat existe déjà
            $this->updateOne($this->panier[$caseIfExists]);
        }
    }

    public function update($newAchat) {
       // var_dump($newAchat['nbQuantity'][0]);
      //  var_dump($newAchat);
       // die();
        

        foreach($newAchat['nbQuantity'] as $key => $quantity){
            if ($quantity == 0) {
                $this->deleteOne($key);
            }
        }
        
        if(count($this->panier) > 0) {
            $nbQuantity = $newAchat["nbQuantity"];
            foreach($this->panier as $key => $achat) {
                $newQuantity = $nbQuantity[$key];
                $achat->update($newQuantity);
            }

            
        }

    }

    private function updateOne($achat)
    {
        $quantite = $achat->getQuantite();
        $achat->setQuantite($quantite + 1);
    }

    public function deleteOne($indexPanier) {
        if(array_key_exists($indexPanier, $this->panier)) {
            unset($this->panier[$indexPanier]);
        }
    }

    private function alreadyExist($product)
    {    if ($this->panier != null) {
        foreach ($this->panier as $i => $achat) {
            if ($achat->getProduct()->getIdProduit() == $product->getIdProduit()) {
                return $i;
            }
        }
        }  
        
        return -1;

    }

    

    public function getPanier()
    {
        return $this->panier;
    }

    public function emptyPanier(){
        $this->panier = [];
    }

    public function getTPS()
    {
        return $this->tps;
    }

    public function getTVQ()
    {
        return $this->tvq;
    }

    public function getSousTotal()
    {
        return $this->sousTotal;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }
    public function getFraisLivraison(): ?float
    {
        return $this->fraisLivraison;
    }

    public function calculateTotal(){
        if ($this->getPanier() != null) {
         
        
        $this->sousTotal = 0;
        foreach ($this->getPanier() as $achat) {
            $this->sousTotal += ($achat->getPrixAchat() * $achat->getQuantite());           
        } 
        
        $this->tvq = round($this->sousTotal * Constantes::TVQ, 2);  
        $this->tps = round($this->sousTotal * Constantes::TPS, 2);  
        $this->total = $this->sousTotal + $this->tvq + $this->tps + $this->fraisLivraison;
    }       
    }
}
