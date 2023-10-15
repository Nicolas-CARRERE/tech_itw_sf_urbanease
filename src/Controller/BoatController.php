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
     * Move boat 4 directions N,S,E,W
     * @Route("/direction/{direction}", name="direction")
     */
    public function moveDirection(string $direction,
                                  MapManager $mapManager,
                                  BoatRepository $boatRepository,
                                  EntityManagerInterface $em,
                                  SessionInterface $session) :Response
    {
        /*Direction boat*/
        $boat = $boatRepository->findOneBy([]);
        $x = $boat->getCoordX();
        $y = $boat->getCoordY();

        if ($direction === 'N') {
            $boat->setCoordY($y - 1);
        }elseif ($direction === 'S'){
            $boat->setCoordY($y + 1);
        }elseif ($direction === 'E'){
            $boat->setCoordX($x + 1);
        }elseif ($direction === 'W'){
            $boat->setCoordX($x - 1);
        }else{
            throw $this->createNotFoundException();
        }

        /*Service tileExists*/
        $exists = $mapManager->tileExists($boatRepository);
        if ($exists) {
            $message = 'La tuile existe.';
            $type = 'info';
        } else {
            $message = 'La tuile n\'existe pas.';
            $type = 'danger';
        }

        /*Limited boat in the map*/
        if($boat->getCoordX() < 0 ){
            $boat->setCoordX(0);
        }
        if($boat->getCoordX() > 12 ){
            $boat->setCoordX(12);
        }
        if($boat->getCoordY() < 0 ){
            $boat->setCoordY(0);
        }
        if($boat->getCoordY() > 6 ){
            $boat->setCoordY(6);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $session->getFlashBag()->add($type, $message);
         return $this->redirectToRoute('map');
    }
}
