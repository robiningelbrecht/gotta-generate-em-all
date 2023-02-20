<?php

namespace App\Domain\Card;

enum CardType: string
{
    case DARK = 'dark';
    case ELECTRIC = 'electric';
    case FIGHTING = 'fighting';
    case FIRE = 'fire';
    case GRASS = 'grass';
    case NORMAL = 'normal';
    case PSYCHIC = 'psychic';
    case STEEL = 'steel';
    case WATER = 'water';
}
