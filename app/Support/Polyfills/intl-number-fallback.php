<?php

if (! extension_loaded('intl') && ! class_exists(\Illuminate\Support\Number::class, false)) {
    require __DIR__.'/NumberFallback.php';
}
