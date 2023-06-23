<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DaySheetController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'login_submit'])->name('login_submit');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [TicketController::class, 'index'])->name('/');
    Route::post('add_ticket', [TicketController::class, 'add_ticket'])->name('ticket.store');
    Route::get('list_ticket', [TicketController::class, 'list_ticket'])->name('ticket.list');
    Route::get('mark_refunded/{ticket_id}', [TicketController::class, 'mark_refunded'])->name('ticket.refund');
    Route::get('delete/{ticket_id}', [TicketController::class, 'delete'])->name('ticket.delete');
    Route::get('get_ticket/{ticket_id}', [TicketController::class, 'get_ticket'])->name('ticket.get_ticket');
    Route::post('edit', [TicketController::class, 'edit'])->name('ticket.edit');
    Route::get('collect/{ticket_id}', [TicketController::class, 'mark_collected'])->name('ticket.collect');
    Route::get('get_collection_balance/{reference_id}', [TicketController::class, 'get_collection_balance']);
    Route::get('generate_report', [TicketController::class, 'generate_report']);

    Route::get('settlements', [SettlementController::class, 'index'])->name('settlements');
    Route::get('list_settlements', [SettlementController::class, 'list_settlements']);
    Route::get('settle_balance/{supplier_id}', [SettlementController::class, 'settle_balance']);
    Route::get('list_transactions/{supplier_id}', [SettlementController::class, 'list_transactions']);

    Route::get('day-sheet', [DaySheetController::class, 'index'])->name('day-sheet');
    Route::post('daysheet/save', [DaySheetController::class, 'save'])->name('daysheet.save');
    Route::get('get-daysheet/{id}', [DaySheetController::class, 'details']);
    Route::get('daysheet/delete/{id}', [DaySheetController::class, 'delete'])->name('daysheet.delete');
});

Route::any('/{page?}',function(){
    return View::make('pages.error.404');
})->where('page','.*');
