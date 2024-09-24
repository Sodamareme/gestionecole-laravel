<?php

use App\Http\Controllers\ApprenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Models\Promotion;
use App\Http\Controllers\PromotionController;
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
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
Route::delete('/oauth/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy']);
Route::post('/oauth/token/refresh', [TransientTokenController::class, 'refresh']);

//  Route::middleware(['auth:api', 'role:Admin'])->group(function () {
Route::prefix('v1/users')->group(function () {
    // ajout users
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/', [UserController::class, 'index'])->name('index');  
    Route::patch('/{id}', [UserController::class, 'update'])->name('update');
    // recherche user par id
    Route::get('/{id}', [UserController::class, 'getUserById'])->name('show');
   
//  });
});
Route::post('v1/Auth/login', [UserController::class, 'login'])->name('login');

// Route::middleware(['auth:api', 'role:Admin,Manager'])->group(function () {
ROute::post('v1/referentiels', [ReferentielController::class, 'store'])->name('store');
Route::get('/users/export/excel', [UserController::class, 'exportExcel']);
Route::get('/users/export/pdf', [UserController::class, 'exportToPDF']);
// lister les referentiels actifs
Route::get('/v1/referentiels', [ReferentielController::class, 'getActiveReferentiels']);
//  lister les referentiel Actif, Inactif ou archiver         
 Route::post('/v1/referentielsStatut', [ReferentielController::class, 'getReferentielsByStatus']);
// lister toutes les referentiels 
Route::get('/v1/referentielsAll', [ReferentielController::class, 'getAllReferentiels']);
// Filtrer par competence c'est a dire liste les competences d'un referentiels et pour chaque competence on affichera ses modules
// Filtrer par modules c'est a dire liste les modules  d'un referentiel
Route::get('/v1/referentiels/{id}', [ReferentielController::class, 'getReferentielById']);
// update referentiel
Route::patch('/v1/referentiels/{id}', [ReferentielController::class, 'update']);
// Supprimer un referentiel en faisant un soft delete
Route::delete('/v1/referentiels/{id}', [ReferentielController::class, 'deleteReferentiel']);
// Lister les referentiels supprimes
Route::get('/v1/archive/referentiels', [ReferentielController::class, 'getArchivedReferentiels']);
// });
// Route::middleware(['auth:api', 'role:Admin,Manager'])->group(function () {
// ajout promotion
Route::post('/v1/promotions', [PromotionController::class, 'store']);
// lister les promotions
Route::get('v1/promotions', [PromotionController::class, 'index']);
// modifier promotion
Route::put('/v1/promotions/{id}', [PromotionController::class, 'update']);
// Ajouter ou retirer un referentiel actif de la promotion
Route::patch('/v1/promotions/{id}/referentiels', [PromotionController::class, 'updateReferentiel']);
// changer l'etat de la promotion
Route::patch('/v1/promotions/{id}/etat', [PromotionController::class, 'updateEtat']); // changer l'etat
// Afficher la promotion en cours
Route::get('/v1/promotions/encours', [PromotionController::class, 'getPromotionEncours']);
// Lister les referentiels actifs d'une promotion
Route::get('/v1/promotions/{id}/referentiels', [PromotionController::class, 'getReferentielsByPromotion']);
// affiche les informations de la promotion 
Route::get('/v1/promotions/{id}/stats', [PromotionController::class, 'getPromotionStats']);
// Cloturer une promotion
Route::patch('/v1/promotions/{id}/cloturer', [PromotionController::class, 'cloturerPromotion']);
// });
// Route::middleware(['auth:api', 'role:Admin,Manager,CM'])->group(function () {
// Permet d'inscrire un apprenant a un referentiel d'une promotion
Route::post('/v1/promotions/{id}/apprenants', [ApprenantController::class, 'inscrireApprenant']);
// Permet inscrire plusieurs apprenants dans un referentiel de la promotion encours a partir d'un fi
Route::post('/v1/apprenants/import', [ApprenantController::class, 'import']);
// lister les apprenants
Route::get('v1/apprenants', [ApprenantController::class, 'index']);
// Pour filtrer par référentiel 
Route::get('v1/apprenants?{referentiel_id}', [ApprenantController::class, 'getApprenantByReferentiel']);
// Pour filtrer par statut (par exemple, "Actif") :
// });

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Authorization Routes (Web Middleware)
// Authenticated Routes
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
$guard = config('passport.guard', null);
Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])->name('authorizations.authorize');
});