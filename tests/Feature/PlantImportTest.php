<?php

use App\Models\Plant;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PlantImportSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(CategorySeeder::class);
    $this->csv = database_path(PlantImportSeeder::CSV);
    $this->hadCsv = is_file($this->csv);
    if ($this->hadCsv) {
        $this->backup = file_get_contents($this->csv);
    }
});

afterEach(function () {
    $this->hadCsv ? file_put_contents($this->csv, $this->backup) : @unlink($this->csv);
});

it('imports nursery rows only, idempotently, without overwriting team edits', function () {
    @mkdir(dirname($this->csv), 0777, true);
    file_put_contents($this->csv, implode("\n", [
        'ID,Name,Category,Price,Description',
        '11,Snake Plant,Nursery,"KES 1,600",Hardy indoor plant',
        '12,Grevillea,Nursery - Seedlings,30,',
        '13,Fan Palm,Nursery,950,',
        '14,Layers Mash,Feeds,3200,',
    ]));

    (new PlantImportSeeder)->run();
    Plant::where('name', 'Snake Plant')->update(['price' => 1800]);
    (new PlantImportSeeder)->run();

    expect(Plant::count())->toBe(3)
        ->and(Plant::where('name', 'Snake Plant')->value('price'))->toBe(1800)
        ->and(Plant::where('name', 'Grevillea')->first()->category->slug)->toBe('tree-seedlings')
        ->and(Plant::where('name', 'Fan Palm')->first()->category->slug)->toBe('palms')
        ->and(Plant::where('name', 'Grevillea')->value('price'))->toBe(30);
});
