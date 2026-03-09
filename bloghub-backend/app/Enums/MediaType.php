<?php

namespace App\Enums;

enum MediaType: string
{
    case Image = 'Image';
    case Gif = 'Gif';
    case Audio = 'Audio';
    case Video = 'Video';
}
