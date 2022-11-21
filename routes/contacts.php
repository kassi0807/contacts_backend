<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get("/contacts", [ContactController::class, "index"]);
Route::post("/contacts", [ContactController::class, "store"]);
Route::post("/contacts/search", [ContactController::class, "search"]);
Route::patch("/contacts/{id}", [ContactController::class, "update"]);
Route::delete("/contacts/{id}", [ContactController::class, "destroy"]);

Route::post("/photos/contacts/{id}", [ContactController::class, "uploadPhoto"]);
Route::get("/photos/{photo}/contacts/{id}", [ContactController::class, "downloadPhoto"]);