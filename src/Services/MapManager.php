<?php

namespace App\Services;

use App\Repository\BoatRepository;

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
}