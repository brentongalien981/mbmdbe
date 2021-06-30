<?php

namespace App\Providers;

use App\Events\PrepareBmdPurchasesCommandEvent;
use App\Events\ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent;
use App\Events\SyncBmdSellerProductsWithInventoryEvent;
use App\Listeners\HandlePrepareBmdPurchasesCommandEvent;
use App\Listeners\HandleResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent;
use App\Listeners\HandleSyncBmdSellerProductsWithInventoryEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PrepareBmdPurchasesCommandEvent::class => [HandlePrepareBmdPurchasesCommandEvent::class],
        SyncBmdSellerProductsWithInventoryEvent::class => [HandleSyncBmdSellerProductsWithInventoryEvent::class],
        ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent::class => [HandleResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent::class]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

}
