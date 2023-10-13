<?php

namespace App\Services;

use App\Entity\Boat; // Import the Boat entity
use App\Entity\Tile;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;

class MapManager
{
    private $tileRepository;
    private $entityManager;

    public function __construct(TileRepository $tileRepository, EntityManagerInterface $entityManager)
    {
        // Constructor to initialize the TileRepository and EntityManager
        $this->tileRepository = $tileRepository;
        $this->entityManager = $entityManager;
    }

    // Method to get a random island tile
    public function getRandomIsland(): ?Tile
    {
        $islandTiles = $this->tileRepository->findBy(['type' => 'island']);

        if (empty($islandTiles)) {
            return null;
        }

        $randomIslandIndex = array_rand($islandTiles);

        return $islandTiles[$randomIslandIndex];
    }

    // Method to check if a tile exists at given coordinates
    public function tileExists(int $x, int $y): bool
    {
        $tile = $this->tileRepository->findOneBy(['coordX' => $x, 'coordY' => $y]);

        return $tile !== null;
    }

    // Method to remove treasure from all tiles
    public function removeTreasure(): void
    {
        $treasureTiles = $this->tileRepository->findBy(['hasTreasure' => true]);

        foreach ($treasureTiles as $tile) {
            $tile->setHasTreasure(false);
            $this->entityManager->persist($tile);
        }

        $this->entityManager->flush();
    }

    // Method to place treasure on a random island tile
    public function placeTreasureOnRandomIsland(): ?Tile
    {
        $randomIslandTile = $this->getRandomIsland();

        if ($randomIslandTile) {
            $randomIslandTile->setHasTreasure(true);
            $this->entityManager->persist($randomIslandTile);
            $this->entityManager->flush();
        }

        return $randomIslandTile;
    }

    // Method to check if the boat has found treasure on its current tile
    public function checkTreasure(Boat $boat): bool
    {
        $tile = $this->tileRepository->findOneBy([
            'coordX' => $boat->getCoordX(),
            'coordY' => $boat->getCoordY(),
            'hasTreasure' => true,
        ]);

        return $tile !== null;
    }
}