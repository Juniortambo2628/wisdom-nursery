<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    public const CATEGORIES = [
        'team' => 'Team',
        'plants' => 'Plants',
        'nursery' => 'Nursery',
        'deliveries' => 'Deliveries',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
