<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Core\Notification;
use App\Core\NotificationColor;
use SebastianBergmann\Environment\Console;

use Stripe;

class StripeController extends AbstractController
{

    private $em = null;
    private $panier = null;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/stripe-checkout', name: 'stripe_checkout')]
    public function stripeCheckout(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        //https:monDomaine.test/stripe-success?session_id={CHECKOUT_SESSION_ID}
        $successURL = $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . "?stripe_id={CHECKOUT_SESSION_ID}";
        $this->initSession($request);
        
        $totalOrder = $this->panier->getTotal() * 100;
        $sessionData = [
            'line_items' => [[
                'quantity' => 1,
                'price_data' => ['unit_amount' => $totalOrder , 'currency' => 'CAD', 'product_data' => ['name' => 'Vincent\'s closet' ]]
            ]],
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $successURL,
            'cancel_url' => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        //Extension curl nécessaire 
        $checkoutSession = \Stripe\Checkout\Session::create($sessionData);
        return $this->redirect($checkoutSession->url, 303);
    }


    #[Route('/stripe-success', name: 'stripe_success')]
    public function stripeSuccess(Request $request) : Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $this->initSession($request);
 
        try {
            
            //TODO: Valider que le paiement ait vraiment fonctionné chez stripe.
            //\Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
            $stripe = new \Stripe\StripeClient($_ENV["STRIPE_SECRET"]);

            $stripeSessionId = $request->query->get('stripe_id');
            $sessionStripe = $stripe->checkout->sessions->retrieve($stripeSessionId);            
            //paymentIntent sera à sauvegarder en BD
            $paymentIntent = $sessionStripe->payment_intent;
                     
            //créer la commande           
           $commande = new Commande($this->panier, $paymentIntent, $user );
           $msgNotification = "";
           $anItemIsBackOrder = false;
           
           foreach ($commande->getAchats() as $achat) {
            $produit = $this->em->merge($achat->getProduct());
            $produit->setQuantiteEnStock($achat->getQuantite());
            $achat->setProduct($produit);
            if($produit->checkIfBackOrder()){
                $this->addFlash('details',new Notification('success', "The product " . $produit->getNom() . " is out of stock ", NotificationColor::INFO));                
                $anItemIsBackOrder = true;
            }                       
            }                 
            $this->em->persist($commande);              
            $this->em->flush();
            //après le flush pour que le idCommande se créer
            if ($anItemIsBackOrder) {
                $this->addFlash('details',new Notification('success', "The order #" . $commande->getIdCommande() . " will be delivered when the products are back in stock", NotificationColor::INFO));
            }
            //Reset cart
            $this->panier->emptyPanier();

            return $this->redirectToRoute('app_details', ['idCommande' => $commande->getIdCommande()]);

        } catch(\Exception $e) {
            //TODO : Redirection
            $this->addFlash('panier',new Notification('Error', "An error as occured with your payment. Please try again.", NotificationColor::DANGER)); 
            return $this->redirectToRoute('app_panier');
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/stripe-cancel', name: 'stripe_cancel')]
    public function stripeCancel() : Response {
        return $this->redirectToRoute('app_panier');
    }


    private function initSession(Request $request)
    {
        $session = $request->getSession();
        $this->panier = $session->get('panier', new Panier());
        $session->set('panier', $this->panier);
    }


}
