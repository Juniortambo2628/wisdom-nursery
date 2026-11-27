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
                Forms\Components\Tabs\Tab::make('Photos')->schema([
                    Forms\Components\Placeholder::make('photos_help')->hiddenLabel()->content(
                        'Tap a box to upload a photo (from a phone is fine), then press Save at the bottom. Landscape photos suit the wide slots. '
                        .'Other photos are changed where they appear: plant photos in Plants, category cards in Plants → Categories, '
                        .'portraits in Content → Team, and the gallery in Content → Gallery.'),
                    ...collect([
                        'Page headers (wide photo behind the page title)' => [
                            'image_hero' => 'Home page',
                            'image_about_banner' => 'About us',
                            'image_services_banner' => 'Services',
                            'image_shop_banner' => 'Our plants (when no category is chosen)',
                            'image_how_banner' => 'How we grow',
                            'image_gallery_banner' => 'Gallery',
                            'image_tips_banner' => 'Grower tips',
                            'image_contact_banner' => 'Contact',
                            'image_quote_banner' => 'Request a quote',
                        ],
                        'Home page: our story' => [
                            'image_story_main' => 'Large photo',
                            'image_story_small' => 'Small photo',
                        ],
                        'How we grow: the four steps (home page and How we grow page)' => [
                            'image_step_1' => '1. Seed',
                            'image_step_2' => '2. Germination',
                            'image_step_3' => '3. Hardening',
                            'image_step_4' => '4. Dispatch',
                        ],
                        'Our facilities (About us page)' => [
                            'image_facility_1' => 'Photo 1',
                            'image_facility_2' => 'Photo 2',
                            'image_facility_wide' => 'Wide photo',
                        ],
                        'Services page: the five cards (tall photos work best)' => collect(range(1, 5))
                            ->mapWithKeys(fn ($i) => ["service_{$i}_image" => ($i).'. '.(setting("service_{$i}_title") ?: "Service {$i}")])->all(),
                    ])->map(fn ($slots, $heading) => Forms\Components\Section::make($heading)->collapsible()->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                        ->schema(collect($slots)->map(fn ($label, $key) => Forms\Components\FileUpload::make($key)->label($label)
                            ->image()->imageEditor()->disk('public')->directory('site')->maxSize(6144))->values()->all()))->values()->all(),
                ]),
                Forms\Components\Tabs\Tab::make('Services')->schema(collect(range(1, 5))->map(fn ($i) => Forms\Components\Fieldset::make("Service {$i} (photo: Photos tab)")->columns(3)->schema([
                    $t("service_{$i}_title", 'Name'),
                    Forms\Components\Textarea::make("service_{$i}_text")->label('Description')->rows(3)->columnSpan(2),
                ]))->all()),
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
