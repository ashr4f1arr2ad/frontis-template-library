<?php

namespace App\Filament\SuperAdmin\Resources\Clouds\Pages;

use App\Filament\SuperAdmin\Resources\Clouds\CloudResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCloud extends ViewRecord
{
    protected static string $resource = CloudResource::class;
    protected string $view = 'filament.resources.clouds.pages.view-cloud';
    public $subscriptions;
    public function mount($record): void
    {
        parent::mount($record);
        $this->subscriptions = $this->getRecord()->user
            ? $this->getRecord()->user->subscriptions()->get()
            : collect();
    }

    protected function getHeaderActions(): array
    {
        return [
//            EditAction::make(),
        ];
    }
}
