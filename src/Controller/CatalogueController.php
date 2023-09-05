<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class CatalogueController extends AbstractController
{
    private $em = null;

    #[Route('/catalogue', name: 'app_catalogue')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        
        $this->em = $doctrine->getManager();
        $categorie = $request->query->get('categorie');
        $categorySelected = $categorie;
        $searchField = $request->request->get('search_field');
        $categories = $this->retrieveCategories();
        


        if ($categorySelected == 1) {
        $produits = $this->retrieveProduits(null,$searchField);
        }
        else
        $produits = $this->retrieveProduits($categorie, $searchField);

        return $this->render('catalogue/index.html.twig', ['categories' => $categories, 'produits' => $produits, 
                                                           'categorySelected' => $categorySelected]);
    }

    #[Route('/catalogue/{idProduit}', name: 'product_modal')]
    public function infoProduit($idProduit, Request $request, ManagerRegistry $doctrine): Response {
        //2 Philosophies -> JSON, HTML

        $this->em = $doctrine->getManager();

        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        return $this->render('catalogue/product.modal.twig', ['produit' => $produit]);

    }

  


    private function retrieveCategories(){
        return $this->em->getRepository(Categorie::class)->findAll();
    }

    private function retrieveProduits($categorie, $searchField){
        return $this->em->getRepository(Produit::class)->findWithCriteria($categorie, $searchField);
    }
    

}

