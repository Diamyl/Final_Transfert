<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Entity\Compte;
use App\Form\DepotFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*obtention du route token*/
/**
 * @Route("/transaction", name="transaction")
 */

class TransactionController extends AbstractController
{
    /**
     * @Route("/transaction", name="transaction")
     */
    public function index()
    {
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

    /**
     * @Route("/depot", name="depot", methods={"POST"})
     */
    public function depot(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, SerializerInterface $serializer)
    {
        $a = $this->getUser()->getId();
        $caissier = $userRepository->find($a); // transformer idcaissier en objet
        
        $depot = new Depot();
        $form = $this->createForm(DepotFormType::class, $depot); //les champs du formulaire
        $datapostman = $request->request->all(); // recupérer les données saisies sur postman
        $form->submit($datapostman); // mettre les données saisies de postman dans le formulaire
        $depot->setDate(new \DateTime());
        $depot->setCaissier($caissier);
        $montant = $depot->getMontant(); // permet d'obtenir le montant saisi sur postman
        if($montant < 75000){
            $data = [
                'Status' => 403,
                'Message' => 'Le montant doit être supérieur à 75 000'
            ];
            return new JsonResponse($data, 201);
        }

        $entityManager->persist($depot); // mapping
        $entityManager->flush(); // insertion dans la database

        $data = [
                'Status' => 201,
                'Message' => 'Dépôt effectué'
            ];
        return new JsonResponse($data, 201);

    }
}
