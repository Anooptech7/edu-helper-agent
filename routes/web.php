<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EduHelperAgentController;

Route::get('/', function () {
    return view('chat');
});

// POST route for the chat
Route::post('/ask-groq', [EduHelperAgentController::class, 'chat'])->name('ask.groq');
