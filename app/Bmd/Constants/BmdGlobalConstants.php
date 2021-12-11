<?php

namespace App\Bmd\Constants;


class BmdGlobalConstants
{

    // BMD-ON-PRESTAGING, BMD-ON-STAGING, BMD-ON-DEPLOYMENT
    // Set the env file.


    // BMD-ON-STAGING
    // public const WEBSITE = 'https://mbmd.com';
    public const BMD_SELLER_NAME = 'ASB Inc.';


    public const RETRIEVED_DATA_FROM_DB = 1001;
    public const RETRIEVED_DATA_FROM_CACHE = 1002;
    public const RETRIEVED_DATA_FROM_LOCAL_STORAGE = 1003;

    // BMD-ON-STAGING: Always set this to desired value like 23.
    public const STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR = 23;


    public const TAX_RATE = 0.13;


    /** BMD-TAGS: constants, consts, inventory, orders, cart */
    // BMD-ON-STAGING: Always set this to desired values.
    public const NUM_OF_DAILY_ORDERS_LIMIT = 50;
    public const NUM_OF_DAILY_ORDER_ITEMS_LIMIT = 200;

    // BMD-ON-STAGING
    // NOTE: Whenever you change this, make sure to edit both frontend and backend constant values.
    public const PAYMENT_TO_FUNDS_PERIOD = 1;
    public const ORDER_PROCESSING_PERIOD = 3;



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-ITER: Development, Staging, Deployment
    public const EMAIL_SENDER_FOR_ORDER_RECEIVED = 'noreply@penguinjam.com';
    public const EMAIL_FOR_ORDER_EMAILS_TRACKER = 'pjtracker23@gmail.com'; // BCC for Order Confirmation Emails
    public const EMAIL_FOR_SHIPPING_ORIGIN_ADDRESS = 'inventory@penguinjam.com';
    // BMD-ON-ITER: Staging: Set this to the dispatch-manager's email.
    public const EMAIL_RECIPIENT_FOR_EP_BATCH_UPDATES = 'inventory@penguinjam.com';
    public const EMAIL_SENDER_FOR_EP_BATCH_UPDATES = 'epbatchupdates@penguinjam.com';



    // BMD-TAGS: email, order, queue, order-received, commands, scheduled-tasks, jobs, purchase, inventory
    // BMD-ON-STAGING
    public const QUEUE_FOR_EMAILING_ORDER_DETAILS = 'TestBmd-QEmailUserOrderDetails';
    public const QUEUE_FOR_HANDLING_MANUAL_SCHEDULED_TASK_DISPATCHES = 'MBMDBE-ManualScheduledTaskDispatchQ';
    public const QUEUE_FOR_HANDLING_LONG_MANUAL_SCHEDULED_TASK_DISPATCHES = 'MBMDBE-LongManualScheduledTaskDispatchQ';
    public const QUEUE_FOR_EP_WEBHOOKS = 'BMDW-EpWebhookQ';



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-STAGING
    public const MBMD_ACCEPTED_EMAIL_DOMAINS = [
        'asbdev.com',
        'penguinjam.com'
    ];



    // 
    public const NUM_OF_SEC_IN_DAY = 24 * 60 * 60;
    public const MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK = 1000;



    /**
     * BMD-SENSITIVE-INFO
     * BMD-ON-STAGING: Update this everytime when it's needed.
     * BMD-ON-ITER: Developemnt, Staging, and Deployment: Change this.
     */
    public const COMPANY_INFO = [
        'company' => 'Penguin Jam',
        'email' => self::EMAIL_FOR_SHIPPING_ORIGIN_ADDRESS,
        'street1' => '82 Laird Dr',
        'street2' => 'Unit 126',
        'city' => 'East York',
        'state' => 'ON',
        'country' => 'CA',
        'zip' => 'M4G3V1',
        'phone' => '6475607078'
    ];



    public const EP_EVENT_DESCRIPTIONS = [
        'BATCH_CREATED' => 'batch.created',
        'BATCH_UPDATED' => 'batch.updated'
    ];



    public const CANADIAN_HOLIDAYS = [
        '2021-12-25',
        '2021-12-26',
        '2021-12-27',
        // 2022
        '2022-1-1',
        '2022-1-3',
        '2022-2-21',
        '2022-4-15',
        '2022-5-23',
        '2022-7-1',
        '2022-9-5',
        '2022-10-10',
        '2022-12-25',
        '2022-12-26',
    ];
}
