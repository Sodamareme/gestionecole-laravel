<?php

use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

use Laravel\Passport\Http\Controllers\{
    AccessTokenController,
    TransientTokenController,
    AuthorizationController,
    ApproveAuthorizationController,
    DenyAuthorizationController,
    AuthorizedAccessTokenController,
    ScopeController,
    PersonalAccessTokenController
};

Route::options('/{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');

// Passport OAuth Routes
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
Route::delete('/oauth/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy']);
Route::post('/oauth/token/refresh', [TransientTokenController::class, 'refresh']);



    // User Routes
    Route::prefix('v1/users')->group(function () {
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::get('/{id}', [UserController::class, 'getUserById'])->name('users.show');
    });

    Route::post('v1/Auth/login', [UserController::class, 'login'])->name('auth.login');

    // Referentiel Routes
    Route::post('v1/referentiels', [ReferentielController::class, 'store'])->name('referentiels.store');
    Route::get('v1/referentiels', [ReferentielController::class, 'getActiveReferentiels']);
    Route::post('v1/referentielsStatut', [ReferentielController::class, 'getReferentielsByStatus']);
    Route::get('v1/referentielsAll', [ReferentielController::class, 'getAllReferentiels']);
    Route::get('v1/referentiels/{id}', [ReferentielController::class, 'getReferentielById']);
    Route::patch('v1/referentiels/{id}', [ReferentielController::class, 'update']);
    Route::delete('v1/referentiels/{id}', [ReferentielController::class, 'deleteReferentiel']);
    Route::get('v1/archive/referentiels', [ReferentielController::class, 'getArchivedReferentiels']);

    // Promotion Routes
    Route::post('v1/promotions', [PromotionController::class, 'store']);
    Route::get('v1/promotions', [PromotionController::class, 'index']);
    Route::put('v1/promotions/{id}', [PromotionController::class, 'update']);
    Route::patch('v1/promotions/{id}/referentiels', [PromotionController::class, 'updateReferentiel']);
    Route::patch('v1/promotions/{id}/etat', [PromotionController::class, 'updateEtat']);
    Route::get('v1/promotions/encours', [PromotionController::class, 'getPromotionEncours']);
    Route::get('v1/promotions/{id}/referentiels', [PromotionController::class, 'getReferentielsByPromotion']);
    Route::get('v1/promotions/{id}/stats', [PromotionController::class, 'getPromotionStats']);
    Route::patch('v1/promotions/{id}/cloturer', [PromotionController::class, 'cloturerPromotion']);

    // Apprenant Routes
    Route::post('v1/promotions/{id}/apprenants', [ApprenantController::class, 'inscrireApprenant']);
    Route::post('v1/apprenants/import', [ApprenantController::class, 'import']);
    Route::get('v1/apprenants', [ApprenantController::class, 'index']);
    Route::get('v1/apprenants', [ApprenantController::class, 'getApprenantByReferentiel']);

    Route::post('v1/enregistrer-notes', [ApprenantController::class, 'enregistrerNotes']);
    

// API Authorization and Tokens Routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/token', [AccessTokenController::class, 'issueToken'])->name('token')->middleware('throttle');
    Route::post('/token/refresh', [TransientTokenController::class, 'refresh'])->name('token.refresh');
    Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])->name('authorizations.approve');
    Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])->name('authorizations.deny');
    Route::get('/tokens', [AuthorizedAccessTokenController::class, 'forUser'])->name('tokens.index');
    Route::delete('/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy'])->name('tokens.destroy');
    Route::get('/scopes', [ScopeController::class, 'all'])->name('scopes.index');
    Route::get('/personal-access-tokens', [PersonalAccessTokenController::class, 'forUser'])->name('personal.tokens.index');
    Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('personal.tokens.store');
    Route::delete('/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('personal.tokens.destroy');
});

// Web middleware and OAuth routes
$guard = config('passport.guard', null);
Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])->name('authorizations.authorize');
});