<?php

namespace App\Controller;

use App\Entity\Tile;
use App\Entity\Boat;
use App\Form\BoatType;
use App\Repository\BoatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MapManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/boat")
 */
class BoatController extends AbstractController
{
    private $mapManager;
    private $session;

    public function __construct(MapManager $mapManager, SessionInterface $session)
    {
        $this->mapManager = $mapManager;
        $this->session = $session;
    }

    /**
     * Move the boat in a specified direction (N, S, E, W)
     * @Route("/direction/{direction}", name="moveDirection", requirements={"direction"="N|S|E|W"})
     */
    public function moveDirection(
        string $direction,
        BoatRepository $boatRepository,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {
        // Log a debug message to indicate that the moveDirection method is called
        $logger->debug('Moving the boat in direction: ' . $direction);

        // Get the current boat entity from the repository
        $boat = $boatRepository->findOneBy([]);

        // Get the current X and Y coordinates of the boat
        $newX = $boat->getCoordX();
        $newY = $boat->getCoordY();

        // Update the X and Y coordinates based on the specified direction
        switch ($direction) {
            case 'N':
                $newY--;
                break;
            case 'S':
                $newY++;
                break;
            case 'E':
                $newX++;
                break;
            case 'W':
                $newX--;
                break;
            default:
                break;
        }

        // Check if the new tile exists on the map
        if (!$this->mapManager->tileExists($newX, $newY)) {
            // Set a flash message for attempting to move off the map
            $this->session->getFlashBag()->add('error', 'Cannot go off the map.');

            // Redirect back to the map without updating the boat's coordinates
            return $this->redirectToRoute('map');
        }

        // Update the boat's coordinates
        $boat->setCoordX($newX);
        $boat->setCoordY($newY);

        // Save the updated boat coordinates to the database
        $em->flush();

        // Check if the boat has found the treasure
        if ($this->mapManager->checkTreasure($boat)) {
            // Display a success flash message
            $this->addFlash('success', 'Congratulations! You found the treasure.');
        }

        // Redirect back to the map
        return $this->redirectToRoute('map');
    }

    /**
     * @Route("/test-move-direction/{direction}", name="test_move_direction", requirements={"direction"="N|S|E|W"})
     */
    public function testMoveDirection(string $direction, BoatRepository $boatRepository, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        // Log a debug message to indicate that the testMoveDirection method is called
        $logger->debug('Testing boat movement in direction: ' . $direction);
    
        // Call the moveDirection method to simulate the boat's movement
        return $this->moveDirection($direction, $boatRepository, $em, $logger);
    }
}
