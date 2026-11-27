<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** Contact details, opening hours, till number and the site's words. */
class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site settings';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $values = Setting::allValues();
        // Phone numbers are a list; older data has one number in "phone".
        $values['phones'] = collect(site_phones())->map(fn ($p) => ['number' => $p['number'], 'label' => $p['label']])->all();
        $this->form->fill($values);
    }

    public function form(Form $form): Form
    {
        $t = fn (string $key, string $label) => Forms\Components\TextInput::make($key)->label($label);
        $a = fn (string $key, string $label) => Forms\Components\Textarea::make($key)->label($label)->rows(3)->columnSpanFull();

        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('Contact')->columns(2)->schema([
                    Forms\Components\Repeater::make('phones')->label('Phone numbers (shown on site)')
                        ->schema([
                            Forms\Components\TextInput::make('number')->label('Number')->type('tel')->required()->maxLength(30)->placeholder('0712 345 678'),
                            Forms\Components\TextInput::make('label')->label('Label (optional)')->maxLength(30)->placeholder('e.g. Orders, Office, Agronomist'),
                        ])
                        ->columns(2)->defaultItems(1)->minItems(1)->maxItems(4)->reorderable()
                        ->addActionLabel('Add another number')
                        ->helperText('The first number is the main one (used for search engines). Up to four.')
                        ->columnSpanFull(),
                    $t('email', 'Email'),
                    $t('address', 'Address'),
                    $t('hours', 'Opening hours'),
                    $t('directions', 'How to find us')->columnSpanFull(),
                    $t('map_embed_url', 'Google Maps embed URL')->url()->helperText('Google Maps → Share → Embed a map → copy the src link.')->columnSpanFull(),
                    $t('map_link', 'Directions link')->url()->columnSpanFull(),
                    $t('latitude', 'Latitude'), $t('longitude', 'Longitude'),
                    $t('social_facebook', 'Facebook')->url(), $t('social_instagram', 'Instagram')->url(), $t('social_tiktok', 'TikTok')->url(),
                ]),
                Forms\Components\Tabs\Tab::make('Ordering')->columns(2)->schema([
                    $t('till_number', 'M-Pesa till or paybill number shown to customers'),
                    $t('mpesa_account', 'Paybill account number')->helperText('For a paybill only. Leave empty for a till.'),
                    $t('collection_note', 'Collection note'),
                    $t('delivery_areas_summary', 'Delivery areas (short)')->helperText('Shown as "Collect, or delivery to …", e.g. "anywhere in Kenya"'),
                    $t('agronomist_hours', 'Agronomist on WhatsApp (days & hours)'),
                    $a('delivery_text', 'Delivery & collection text'),
                    $a('care_text', 'Planting & care text (plant pages)'),
                ]),
                Forms\Components\Tabs\Tab::make('Home page')->columns(2)->schema([
                    $t('since_year', 'Since (year)'),
                    $t('site_tagline', 'Tagline'),
                    $t('story_title', 'Our story heading')->columnSpanFull(),
                    $a('story_text', 'Our story'),
                    $t('stat_1_value', 'Stat 1 number'), $t('stat_1_label', 'Stat 1 label'),
                    $t('stat_2_value', 'Stat 2 number'), $t('stat_2_label', 'Stat 2 label'),
                    $t('stat_3_value', 'Stat 3 number'), $t('stat_3_label', 'Stat 3 label'),
                    $t('stat_4_value', 'Stat 4 number'), $t('stat_4_label', 'Stat 4 label'),
                    $t('how_we_grow_1', 'Step 1: Seed'), $t('how_we_grow_2', 'Step 2: Germination'),
                    $t('how_we_grow_3', 'Step 3: Hardening'), $t('how_we_grow_4', 'Step 4: Dispatch'),
                ]),
                Forms\Components\Tabs\Tab::make('Photos')->columns(3)->schema(collect([
                    'image_hero' => 'Home: large top photo',
                    'image_story_main' => 'Home: our story (large)',
                    'image_story_small' => 'Home: our story (small)',
                    'image_step_1' => 'How we grow 1: Seed',
                    'image_step_2' => 'How we grow 2: Germination',
                    'image_step_3' => 'How we grow 3: Hardening',
                    'image_step_4' => 'How we grow 4: Dispatch',
                    'image_shop_banner' => 'Our plants: wide photo',
                    'image_about_banner' => 'About: team photo',
                    'image_facility_1' => 'Facilities 1',
                    'image_facility_2' => 'Facilities 2',
                    'image_facility_wide' => 'Facilities: wide photo',
                ])->map(fn ($label, $key) => Forms\Components\FileUpload::make($key)->label($label)
                    ->image()->imageEditor()->disk('public')->directory('site')->maxSize(6144))->values()->all()),
                Forms\Components\Tabs\Tab::make('About')->columns(2)->schema([
                    $a('about_text', 'About us'),
                    $a('facilities_text', 'Facilities'),
                    Forms\Components\Textarea::make('facility_units')->label('Nursery units (one per line)')->rows(5)
                        ->helperText('Listed under "Our facilities", e.g. Kilimanjaro Greenhouse.'),
                    $t('facility_certification', 'Certification (optional)'),
                    Forms\Components\Fieldset::make('What we grow (About page, four boxes)')->columns(2)->columnSpanFull()->schema(collect(range(1, 4))->flatMap(fn ($i) => [
                        $t("about_focus_{$i}_title", "Box {$i} heading"),
                        Forms\Components\Textarea::make("about_focus_{$i}_text")->label("Box {$i} text")->rows(2),
                    ])->all()),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $phones = collect($state['phones'] ?? [])
            ->map(fn ($p) => ['number' => trim((string) ($p['number'] ?? '')), 'label' => trim((string) ($p['label'] ?? '')) ?: null])
            ->filter(fn ($p) => $p['number'] !== '')->values()->all();
        $state['phones'] = json_encode($phones, JSON_UNESCAPED_UNICODE);
        $state['phone'] = $phones[0]['number'] ?? null; // main number, kept for anything that reads one
        foreach ($state as $key => $value) {
            Setting::put($key, $value);
        }
        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [Action::make('save')->label('Save')->submit('save')];
    }
}
