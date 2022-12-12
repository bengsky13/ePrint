<?php
require_once 'vendor/autoload.php';
use Rawilk\Printing;

$printJob = Printing::newPrintTask()
    ->printer($printerId)
    ->file('path_to_file.pdf')
    ->send();

$printJob->id();