<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Home / Static Pages
|--------------------------------------------------------------------------
*/
Route::get('/', [BookController::class, 'index'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

/*
|--------------------------------------------------------------------------
| Admin / Librarian Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:librarian|admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Book Management
    Route::get('/admin/books', [BookController::class, 'index'])->name('books.manage');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // Books Data Page
    Route::get('/admin/books-data', function () {
        return view('books.books_data');
    })->name('books.data');

    // Author & Category Management
    Route::resource('authors', AuthorController::class);
    Route::resource('categories', CategoryController::class);

    // User Management
    Route::get('/admin/users', [UserController::class, 'manage'])->name('users.manage');
    Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Loan Management
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
    Route::patch('/admin/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('admin.loans.return');
    Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
});

/*
|--------------------------------------------------------------------------
| Authenticated Book Routes (All logged-in users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
});

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:member'])->group(function () {

    // Dashboard
    Route::get('/user/dashboard', [UserController::class, 'index'])->name('user.dashboard');

    // Borrow / Return Books
    Route::post('/books/{book}/borrow', [LoanController::class, 'borrow'])->name('loans.borrow');
    Route::post('/books/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');

    // Pay deferred loans
    Route::get('/loans/{loan}/pay-deferred', [LoanController::class, 'payDeferredLoan'])->name('loans.payDeferred');

    // My loans page
    Route::get('/my-loans', [LoanController::class, 'myLoans'])->name('loans.my');

    // Stripe success callback for instant borrow
    Route::get('/books/{book}/borrow-success', [LoanController::class, 'borrowSuccess'])->name('loans.borrow.success');

    // Stripe success callback for deferred payment
    Route::get('/loans/{loan}/pay-success', [LoanController::class, 'paySuccess'])->name('loans.pay.success');
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/payment/checkout/{loan}', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/success/{loan}', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel/{loan}', [PaymentController::class, 'cancel'])->name('payment.cancel');
});
