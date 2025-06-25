<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $summary = ActivityController::getDailySummary(); // Ambil data ringkasan order
        $onlinePorterCount = PorterController::countOnlinePorters(); // Ambil jumlah porter online

        return view("dashboard.index", compact('summary', 'onlinePorterCount')); // Kirim dua data ke blade
    }

}
