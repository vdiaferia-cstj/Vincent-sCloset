<?php

namespace App\Controller;


use App\Entity\Achat;
use App\Core\Constantes;
use App\Core\Notification;
use App\Core\NotificationColor;
use App\Entity\Panier;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\TextUI\XmlConfiguration\Constant;
use Symfony\Component\Validator\Constraints\Length;

class PanierController extends AbstractController
{
    private $panier;
    private $em = null;
    
    #[Route('/review', name: 'app_review')]
    public function review(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->initSession($request);       
        if (empty($this->panier->getPanier())) {
            
            return $this->render('profile/login.html.twig' , ['notification' => null, 'last_username' => null ]);
        }
        
        $this->panier->calculateTotal();
        return $this->render('panier/review.html.twig', [
            'panier' => $this->panier
        ]);
    }

    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request): Response
    {
        
        $this->initSession($request);
        $this->panier->calculateTotal();
        return $this->render('panier/index.html.twig', [
            'panier' => $this->panier
        ]);
    }

    #[Route('/panier/ajout/{idProduit}', name: 'app_add')]
    public function add($idProduit, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->em = $doctrine->getManager();
        $this->initSession($request);
        $produit = $this->em->find(Produit::class, $idProduit);
        $this->panier->add($produit);
        $this->addFlash('panier',new Notification('success', 'Item has been added to the cart', NotificationColor::SUCCESS));
        $this->panier->calculateTotal();

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/delete/{index}', name: 'app_deleteOne')]
    public function deleteOne($index, Request $request): Response
    {
        $this->initSession($request);
        $this->panier->deleteOne($index);
        $this->addFlash('panier',new Notification('sucess', 'Item has been deleted', NotificationColor::DANGER));
        $this->panier->calculateTotal();
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/update', name:'panier_update', methods:['POST'])]
    public function updatePanier(Request $request) : Response {
        $post = $request->request->all();
        $this->initSession($request);
        $isValid = true;

        $action = $request->request->get('action');
        
        foreach ($post['nbQuantity'] as $key => $quantite) {
            if (!is_numeric($quantite) || $quantite < 0) {
                $isValid = false;
            }
        }
        if ($isValid) {    
                     
        if($action == "update") {
            $this->panier->update($post);
            $this->addFlash('panier',new Notification('success', 'Cart has been updated', NotificationColor::INFO));
        }  
        if($action == "empty") {
            $session = $request->getSession();
            $session->remove('panier');
            $this->addFlash('panier',new Notification('success', 'Cart has been cleared', NotificationColor::DANGER));

        }    
         if ($action == "review") {
           
            return $this->redirectToRoute('app_review');
        }
        
        $this->panier->calculateTotal();     
        }
        return $this->redirectToRoute('app_panier');
    }


    private function initSession(Request $request)
    {
        $session = $request->getSession();
        $this->panier = $session->get('panier', new Panier());
        $session->set('panier', $this->panier);
    }

    
}
