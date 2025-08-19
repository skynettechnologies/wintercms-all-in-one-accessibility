<?php namespace Skynettechnologies\Allinoneaccessibility\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;

class Dashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Sets the backend menu context so the correct menu highlights
        BackendMenu::setContext('Skynettechnologies.Allinoneaccessibility', 'allinoneaccessibility');
    }

    public function index()
    {
        $this->pageTitle = 'Accessibility Dashboard';
    }
}
