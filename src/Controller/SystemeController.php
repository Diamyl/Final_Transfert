<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
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

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param JWTEncoderInterface $JWTEncoder
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function login(Request $request, UserPasswordEncoderInterface $passwordEncoder,  JWTEncoderInterface $JWTEncoder)
    {
        $datapostman = $request->request->all();
        $email = $datapostman['email'];
        $password = $datapostman['password'];
        $repository = $this->getDoctrine()->getRepository(User::class);
        $comparemail = $repository->findOneBy(['email' => $email]); // comparer l'email saisi avec l'emailse trouvant dans la database
       
        if($comparemail==true){
            $comparpassword = $passwordEncoder->isPasswordValid($comparemail, $password);
            if($comparpassword){

                $token = $JWTEncoder->encode([
                    'email' => $comparemail->getEmail(),
                    'exp' => time() + 3600 // 1 hour expiration
                ]);

                return new JsonResponse(['token' => $token]);
            }

            else{
                $data = [
                    'status' => 500,
                    'message' => 'Password Wrong',
                ];

                return new JsonResponse($data, 201);
            }

            $data = [
                'status' => 201,
                'message' => 'Done',
            ];

            return new JsonResponse($data, 201);
        }
        else {
            $data = [
                'status' => 500,
                'message' => 'wrong',
            ];

            return new JsonResponse($data, 201);
        }


    }
}
