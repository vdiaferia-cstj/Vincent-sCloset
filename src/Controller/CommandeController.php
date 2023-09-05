<?php

namespace App\Controller;

use App\Core\Notification;
use App\Core\NotificationColor;
use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
class CommandeController extends AbstractController
{
  

    #[Route('/orders', name: 'app_commande')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $commandes = $user->getCommandes();

        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
            'commandes' => $commandes,
            'user' => $user
        ]);
    }

    #[Route('/orders/{idCommande}', name: 'app_details')]
    public function details($idCommande): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $commandes = $user->getCommandes();
        $commandeSelect = null;
        $isTheOwner = false;

        // aller chercher la commande qu'on veut les details
        foreach ($commandes as $commande){
            if ($commande->getIdCommande() == $idCommande) {
                $commandeSelect = $commande;
                $isTheOwner = true;
                break;
            }
        }
        // pour savoir s'il essaie d'acceder à une commande qui ne lui appartient pas
        if ($isTheOwner) {
            return $this->render('commande/details.html.twig', [
                'commande' => $commandeSelect,
                'user' => $user
            ]);
        }
        // si oui, on le redirige vers ses propres commandes
        else
        {
            return $this->render('commande/index.html.twig', [
                'commandes' => $commandes,
                'user' => $user
            ]);
        }
        
    }

}
