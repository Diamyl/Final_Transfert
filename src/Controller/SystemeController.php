<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SystemeController extends AbstractController
{
    /**
     * @Route("/systeme", name="systeme")
     */
    public function index()
    {
        return $this->render('systeme/index.html.twig', [
            'controller_name' => 'SystemeController',
        ]);
    }

    /**
     * @Route("/addusersysteme", name="addusersysteme", methods={"POST"})
     */
    public function addusersystem(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer)
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user); //les champs du formulaire
        $datapostman = $request->request->all(); // recupérer les données saisies sur postman
        $form->submit($datapostman); // mettre les données saisies de postman sur le formulaire
        $password = $datapostman['password']; // recuperation de ts les données du tableau datapostman dont le password
        $encodpassword = $passwordEncoder->encodePassword($user,$password); // encodage du password aprés recupération des données du user
        $user->setPassword($encodpassword); // Repasser le password
        $profil = $datapostman['profil'];
        if($profil==1){
            $user->setRoles(["ROLE_SUPERADMIN"]); // le champs Role
            $user->setProfil('SUPER ADMIN'); // le champs profil
        }

        elseif ($profil == 2) {
            $user->setRoles(["ROLE_ADMIN"]);
            $user->setProfil('ADMIN');
        } 
        
        else {
            $user->setRoles(["ROLE_CAISSIER"]);
            $user->setProfil('CAISSIER');
        }

        $entityManager->persist($user);// mapping
        $entityManager->flush(); // insertion dans la database

        $data = $serializer->serialize($user, 'json'); // conversion en Json

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);

    }
}
