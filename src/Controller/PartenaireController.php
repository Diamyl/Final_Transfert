<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Partenaire;
use App\Form\UserFormType;
use App\Form\PartenaireFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/partenaire", name="partenaire")
 */
class PartenaireController extends AbstractController
{

    /**
     * @Route("/addpartenaire", name="addpartenaire", methods={"POST"})
     */
    public function addpartenaire(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer)
    {
        $a = $this->getUser();
        $idcreateur= $a->getId();  
        $createur=$this->getDoctrine()->getRepository(User::class)->find($idcreateur); // transformer idcreateur en objet
        
        $partenaire = new Partenaire();
        $partenaire->setStatus('Actif');
        $form = $this->createForm(PartenaireFormType::class, $partenaire); //les champs du formulaire

        $user = new User();
        $formuser = $this->createForm(UserFormType::class, $user); //les champs du formulaire
        $datapostman = $request->request->all(); // recupérer les données saisies sur postman
        $form->submit($datapostman); // mettre les données saisies de postman dans le formulaire
        $formuser->submit($datapostman); // mettre les données saisies de postman dans le formulaire
        $password = $datapostman['password']; // recuperation du password à partir des données saisies sur postman
        $encodpassword = $passwordEncoder->encodePassword($user, $password); // encodage du password aprés recupération des données du user
        $user->setPassword($encodpassword); // Repasser le password
        $user->setRoles(["ROLE_SUPER_ADMIN_PARTENAIRE"]);
        $user->setProfil('SUPER ADMIN PARTENAIRE');
        $user->setStatus('Actif');
        $user->setPartenaire($partenaire); // id du partenaire pour le user

        $compte = new Compte();
        $ncompte = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $compte->setNumero($ncompte);
        $compte->setMontant(0);
        $compte->setPartenaire($partenaire); // id du partenaire pour le compte
        $compte->setCreateur($createur); // id du admin créateur pour le compte

        $entityManager->persist($partenaire); // mapping
        $entityManager->persist($user); // mapping
        $entityManager->persist($compte); // mapping
        $entityManager->flush(); // insertion dans la database

        $data = $serializer->serialize($user, 'json'); // conversion en Json

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/adduserpartenaire", name="adduserpartenaire", methods={"POST"})
     */
    public function adduserpartenaire(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer)
    {
        $idpartenaire = $this->getUser()->getPartenaire(); //recuperation de l'id du partenaire de l'admin qui s'est connecté
        
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user); //les champs du formulaire
        $datapostman = $request->request->all(); // recupérer les données saisies sur postman
        $form->submit($datapostman); // mettre les données saisies de postman dans le formulaire
        $password = $datapostman['password']; // recuperation du password à partir des données saisies sur postman
        $encodpassword = $passwordEncoder->encodePassword($user, $password); // encodage du password aprés recupération des données du user
        $user->setPassword($encodpassword); // Repasser le password 
        $user->setStatus('Actif');
        $user->setPartenaire($idpartenaire);
        $profil = $datapostman['profil']; // recuperation du profil à partir des données saisies sur postman

        if ($profil == 1){
            $user->setRoles(["ROLE_ADMIN_PARTENAIRE"]);
            $user->setProfil('USER ADMIN PARTENAIRE');

            $data = [
                'Status' => 201,
                'Message' => 'Admin Partenaire créé'
            ];
        } 

        else {
            $user->setRoles(["ROLE_USER_PARTENAIRE"]);
            $user->setProfil('USER PARTENAIRE');

            $data = [
                'Status' => 201,
                'Message' => 'User Partenaire créé'
            ];
        } 

        $entityManager->persist($user); // mapping
        $entityManager->flush(); // insertion dans la database

        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/addcompte/{id}", name="addcompte", methods={"POST"})
     */
    public function addcompte(Partenaire $partenaire,Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, SerializerInterface $serializer)
    {
        $idcreateur = $this->getUser(); //recuperation de l'id du créateur
        
        $compte = new Compte();
        $ncompte = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $compte->setNumero($ncompte);
        $compte->setMontant(0);
        $compte->setPartenaire($partenaire); // id du partenaire pour le compte
        $compte->setCreateur($idcreateur); // id du admin créateur pour le compte

        $entityManager->persist($compte); // mapping
        $entityManager->flush(); // insertion dans la database

        $data = [
            'Status' => 201,
            'Message' => 'Compte créé'
        ];
        return new JsonResponse($data, 201);
    }
}
