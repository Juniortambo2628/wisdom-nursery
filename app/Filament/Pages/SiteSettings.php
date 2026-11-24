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
        $this->form->fill(Setting::allValues());
    }

    public function form(Form $form): Form
    {
        $t = fn (string $key, string $label) => Forms\Components\TextInput::make($key)->label($label);
        $a = fn (string $key, string $label) => Forms\Components\Textarea::make($key)->label($label)->rows(3)->columnSpanFull();

        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('Contact')->columns(2)->schema([
                    $t('phone', 'Phone (shown on site)'),
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
                    $t('till_number', 'M-Pesa till / paybill shown to customers'),
                    $t('collection_note', 'Collection note'),
                    $t('delivery_areas_summary', 'Delivery areas (short)')->helperText('e.g. "Kitui and Machakos"'),
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
                Forms\Components\Tabs\Tab::make('About')->columns(2)->schema([
                    $a('about_text', 'About us'),
                    $a('facilities_text', 'Facilities'),
                    $t('facility_1', 'Facility 1'), $t('facility_2', 'Facility 2'),
                    $t('facility_certification', 'Certification'),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::put($key, $value);
        }
        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [Action::make('save')->label('Save')->submit('save')];
    }
}
