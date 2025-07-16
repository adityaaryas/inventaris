<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', Dashboard::class)->name('back.dashboard');