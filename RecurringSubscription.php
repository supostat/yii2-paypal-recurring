<?php

namespace purrweb;

class RecurringSubscription {

    const DAY = "Day";
    const WEEK = "Week";
    const SEMI_MONTH = "SemiMonth";
    const MONTH = "Month";
    const YEAR = "Year";

    public $PAYERID;
    public $PROFILESTARTDATE;
    public $DESC;
    public $BILLINGPERIOD;
    public $INITAMT;
    public $FAILEDINITAMTACTION = 'CancelOnFailure';
    public $BILLINGFREQUENCY;
    public $AMT;
}