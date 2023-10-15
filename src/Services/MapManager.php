<?php

namespace App\Services;

use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use App\Entity\Tile;

class MapManager
{

    /**
     * check if the tile exists on the map
     * @param BoatRepository $boatRepository
     * @return bool
     */
    public function tileExists(BoatRepository $boatRepository): bool
    {
        $boat = $boatRepository->findOneBy([]);
        $boatX = $boat->getCoordX();
        $boatY = $boat->getCoordY();
        if ($boatX < 0 || $boatX > 12 || $boatY < 0 || $boatY > 6) {
            return false;

        }

        return true;
    }

    /**
     * select a random island
     * @param TileRepository $tileRepository
     * @return Tile|null
     */
    public function getRandomIsland(TileRepository $tileRepository): ?Tile
    {
        $tiles = $tileRepository->findBy(['type' => 'island']);

        if (!$tiles) {
            return null;
        }

        $randomTile = $tiles[array_rand($tiles)];



        return $randomTile;
    }

    /**
     * check if the boat is on the treasure
     * @param BoatRepository $boatRepository
     * @param TileRepository $tileRepository
     * @return bool
     */
    public function checkTreasure(BoatRepository $boatRepository,
                                  TileRepository $tileRepository): bool
    {
        $boat = $boatRepository->findOneBy([]);
        $tile = $tileRepository->findOneBy(['hasTreasure' => true]);

        $boatX = $boat->getCoordX();
        $boatY = $boat->getCoordY();
        $tileY = $tile->getCoordY();
        $tileX = $tile->getCoordX();

        if ($boatX === $tileX && $boatY === $tileY){
            return true;
        }
        return false;

    }
}