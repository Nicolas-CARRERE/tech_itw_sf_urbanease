<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tile;
use App\Entity\Boat; // Import the Boat entity
use App\Repository\BoatRepository;
use App\Services\MapManager;
use Doctrine\ORM\EntityManagerInterface;

class MapController extends AbstractController
{
    /**
     * Display the map and boat's current position.
     *
     * @Route("/map", name="map")
     */
    public function displayMap(BoatRepository $boatRepository): Response
    {
        // Get the Entity Manager
        $em = $this->getDoctrine()->getManager();

        // Retrieve all tiles from the database
        $tiles = $em->getRepository(Tile::class)->findAll();

        // Create a map structure using tile coordinates as keys
        foreach ($tiles as $tile) {
            $map[$tile->getCoordX()][$tile->getCoordY()] = $tile;
        }

        // Retrieve the current boat entity
        $boat = $boatRepository->findOneBy([]);

        // Render the map view with map data and boat information
        return $this->render('map/index.html.twig', [
            'map'  => $map ?? [], // Pass the map data to the view (or an empty array if none)
            'boat' => $boat,     // Pass the boat entity to the view
        ]);
    }

    /**
     * Start a new game by resetting the boat's coordinates and placing the treasure on a random island.
     *
     * @Route("/start", name="start_game")
     */
    public function start(MapManager $mapManager, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Reset the boat's coordinates to (0, 0)
        $boat = $this->getDoctrine()->getRepository(Boat::class)->findOneBy([]);
        $boat->setCoordX(0);
        $boat->setCoordY(0);

        // Remove any existing treasure from the map
        $mapManager->removeTreasure();

        // Place the treasure on a random island
        $randomIsland = $mapManager->placeTreasureOnRandomIsland();
        if ($randomIsland) {
            $randomIsland->setHasTreasure(true);
            $entityManager->persist($randomIsland);
            $entityManager->flush();
        }

        // Redirect to the map page after starting the game
        return $this->redirectToRoute('map');
    }
}