<?php

namespace App\Controller;

use App\Entity\Boat;
use App\Form\BoatType;
use App\Repository\BoatRepository;
use App\Services\MapManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boat")
 */
class BoatController extends AbstractController
{
    public const DIRECTIONS = [
        'N' => [0, -1],
        'S' => [0, 1],
        'E' => [1, 0],
        'W' => [-1, 0],
    ];
    /**
     * Move the boat to coord x,y
     * @Route("/move/{x}/{y}", name="moveBoat", requirements={"x"="\d+", "y"="\d+"}))
     */
    public function moveBoat(int $x, int $y, BoatRepository $boatRepository, EntityManagerInterface $em) :Response
    {
        $boat = $boatRepository->findOneBy([]);
        $boat->setCoordX($x);
        $boat->setCoordY($y);

        $em->flush();

        return $this->redirectToRoute('map');
    }


    /**
     * @Route("/", name="boat_index", methods="GET")
     */
    public function index(BoatRepository $boatRepository): Response
    {
        return $this->render('boat/index.html.twig', ['boats' => $boatRepository->findAll()]);
    }

    /**
     * @Route("/new", name="boat_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $boat = new Boat();
        $form = $this->createForm(BoatType::class, $boat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($boat);
            $em->flush();

            return $this->redirectToRoute('boat_index');
        }

        return $this->render('boat/new.html.twig', [
            'boat' => $boat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boat_show", methods="GET")
     */
    public function show(Boat $boat): Response
    {
        return $this->render('boat/show.html.twig', ['boat' => $boat]);
    }

    /**
     * @Route("/{id}/edit", name="boat_edit", methods="GET|POST")
     */
    public function edit(Request $request, Boat $boat): Response
    {
        $form = $this->createForm(BoatType::class, $boat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('boat_index', ['id' => $boat->getId()]);
        }

        return $this->render('boat/edit.html.twig', [
            'boat' => $boat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boat_delete", methods="DELETE")
     */
    public function delete(Request $request, Boat $boat): Response
    {
        if ($this->isCsrfTokenValid('delete' . $boat->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($boat);
            $em->flush();
        }

        return $this->redirectToRoute('boat_index');
    }

    /**
     * @Route("/direction/{direction}", name="direction")
     */
    public function moveDirection(string $direction, MapManager $mapManager,BoatRepository
    $boatRepository, EntityManagerInterface $em,SessionInterface $session) :Response
    {
        $boat = $boatRepository->findOneBy([]);
        if ($direction === 'N') {
            $boat->setCoordY($boat->getCoordY() - 1);
        }elseif ($direction === 'S'){
            $boat->setCoordY($boat->getCoordY() + 1);
        }elseif ($direction === 'E'){
            $boat->setCoordX($boat->getCoordX() + 1);
        }elseif ($direction === 'W'){
            $boat->setCoordX($boat->getCoordX() - 1);
        }else{
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $x = $boat->getCoordX();
        $y = $boat->getCoordY();

        $exists = $mapManager->tileExists($boatRepository);
        if ($exists) {
            $message = 'La tuile existe.';
            $type = 'info';
        } else {
            $message = 'La tuile n\'existe pas.';
            $type = 'danger';
        }

        $session->getFlashBag()->add($type, $message);


        return $this->redirectToRoute('map');
    }
}
