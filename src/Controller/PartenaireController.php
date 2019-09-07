<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Partenaire;
use App\Form\UserFormType;
use App\Form\PartenaireFormType;
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
        $user->setRoles(["ROLE_ADMIN_PARTENAIRE"]);
        $user->setProfil('ADMIN PARTENAIRE');
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

        
   
}
