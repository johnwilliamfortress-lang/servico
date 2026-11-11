<?php

use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/anuncios/novo', [AnnouncementController::class, 'create'])->name('announcements.create');
Route::post('/anuncios', [AnnouncementController::class, 'store'])->name('announcements.store');
Route::get('/anuncios/{announcement}/editar/{code}', [AnnouncementController::class, 'edit'])->name('announcements.edit');
Route::put('/anuncios/{announcement}/editar/{code}', [AnnouncementController::class, 'update'])->name('announcements.update');
Route::delete('/anuncios/{announcement}/editar/{code}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
