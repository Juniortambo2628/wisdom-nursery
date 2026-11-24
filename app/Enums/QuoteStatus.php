<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case New = 'new';
    case Replied = 'replied';
    case Won = 'won';
    case Lost = 'lost';
}
