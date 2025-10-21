<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('Pages/Privacy');
    }

    /**
     * Display the terms of service page.
     */
    public function terms(): Response
    {
        return Inertia::render('Pages/Terms');
    }

    /**
     * Display the contact us page.
     */
    public function contact(): Response
    {
        return Inertia::render('Pages/Contact');
    }

    /**
     * Display the feedback page.
     */
    public function feedback(): Response
    {
        return Inertia::render('Pages/Feedback');
    }

    /**
     * Display the credits withdrawal page.
     */
    public function creditsWithdraw(): Response
    {
        return Inertia::render('Pages/CreditsWithdraw');
    }

    /**
     * Display the points rules page.
     */
    public function pointsRules(): Response
    {
        return Inertia::render('Pages/PointsRules');
    }

    /**
     * Display the about us page.
     */
    public function about(): Response
    {
        return Inertia::render('Pages/About');
    }
}
