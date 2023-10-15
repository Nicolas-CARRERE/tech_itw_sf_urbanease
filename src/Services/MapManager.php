<?php

namespace App\Services;

use App\Entity\Tile;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;

class MapManager
{
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

    public function getRandomIsland(TileRepository $tileRepository): ?Tile
    {
        $tiles = $tileRepository->findBy(['type' => 'island']);

        if (!$tiles) {
            return null;
        }

        $randomTile = $tiles[array_rand($tiles)];



        return $randomTile;
    }
}