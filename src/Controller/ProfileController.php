<?php

namespace App\Controller;

use App\Core\Notification;
use App\Core\NotificationColor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Client;
use App\Form\ModificationFormType;
use App\Form\ModificationPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();

        //form Informations et form update password
        $formUpdate = $this->createForm(ModificationFormType::class, $currentUser);
        $formPassword = $this->createForm(ModificationPasswordType::class);
        $formPassword->handleRequest($request);
        $formUpdate->handleRequest($request);

        $notificationPassword = null;
        $notificationUpdate = null;


        if ($formUpdate->isSubmitted() ) {
            // si les champs du formulaire sont valides
            if ( $formUpdate->isValid()) {
            $notificationUpdate = new Notification('Success', "Your informations has been updated.", NotificationColor::SUCCESS);
            $entityManager->flush();
            }
            else {
                $notificationUpdate = new Notification('error', "Please fix the errors in the form.", NotificationColor::WARNING);
            }
        }
       
        // si le bouton est cliqué    
        if ($formPassword->isSubmitted()) {
            // si le password respecte les forms ( 6 charactères minimum ) 
            if ($formPassword->isValid()) {           
            // si l'ancien mot de passe entré est celui du user
            if ($userPasswordHasher->isPasswordValid($currentUser, $formPassword->get('oldPassword')->getData())) {
                
                //si l'ancien mot de passe et le nouveau ne sont pas les même
                if ($formPassword->get('oldPassword')->getData() != $formPassword->get('newPassword')->getData()) {
                    $currentUser->setPassword(
                        $userPasswordHasher->hashPassword(
                            $currentUser,
                            $formPassword->get('newPassword')->getData()
                        )
                    );
                    $notificationPassword = new Notification('Success', "Your password has been updated.", NotificationColor::SUCCESS);
                }
                else {
                    $notificationPassword = new Notification('error', "Your new password must be different than your last.", NotificationColor::WARNING);
                }
                
            } else {
                $notificationPassword = new Notification('error', "Please enter your current password to update it.", NotificationColor::WARNING);
            }
            $entityManager->flush();
        }
        else {
            $notificationPassword = new Notification('error', "Please enter a minimum of 6 characters and the same password to change it.", NotificationColor::WARNING);
        }
    }
        

        return $this->render('profile/index.html.twig', [
            'currentUser' => $currentUser,
            'modificationForm' => $formUpdate->createView(),
            'modificationPasswordForm' => $formPassword->createView(),
            'notificationPassword' => $notificationPassword,
            'notificationUpdate' => $notificationUpdate
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $notification = null;
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error != null && $error->getMessageKey() === 'Invalid credentials.') {
            $message = "Wrong combinaison between email and password.";
            $notification = new Notification('error', $message, NotificationColor::WARNING);
        }
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('profile/login.html.twig', [
            'last_username' => $lastUsername,
            'notification' => $notification
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}
