<?php
use Illuminate\Http\Request;
use App\Http\Controllers\QuizSubmissionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WhoAmIController;
use App\Http\Controllers\ChallengeListController;
use App\Http\Controllers\ChallengeSubmissionController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DailyChallengeController;
use App\Http\Controllers\DailyChallengeSubmissionController;
use App\Http\Controllers\DailyStreakController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuizController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['authenticate'])->group(function () {

    //Login
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/regenerate-token', [LoginController::class, 'regenerateToken']);
    Route::post('/user/update', [UserController::class, 'updateUser']);


    //Achievements
    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::post('/achievements', [AchievementController::class, 'store']);
    Route::get('/user-achievements/{userId}', [AchievementController::class, 'userAchievements']);
    Route::post('/user-achievements', [AchievementController::class, 'addUserAchievement']);

    //Challenges
    Route::post('/challenges/complete', [ChallengeController::class, 'complete']);
    Route::get('/challenges/score/{userId}', [ChallengeController::class, 'score']);
    Route::get('/challenges/results/{userId}/{challengeId}/{type}', [ChallengeController::class, 'results']);
    Route::get('/challenges/random/{userId}/{type}', [ChallengeController::class, 'randomUnanswered']);
    Route::get('/challenges/search', [ChallengeController::class, 'search']);

    //Challenge List
    Route::get('challenge-list', [ChallengeListController::class, 'getChallenges']);
    Route::get('challenge-list/{id}', [ChallengeListController::class, 'getChallengeById']);
    Route::post('challenge-list', [ChallengeListController::class, 'addChallenge']);

    //Challenge Submission
    Route::post('user-challenge-answer', [ChallengeSubmissionController::class, 'submitAnswer']);
    Route::get('user-challenge-answer', [ChallengeSubmissionController::class, 'getUserAnswers']);
    Route::post('finish-challenge', [ChallengeSubmissionController::class, 'finishChallenge']);

    //Competitions
    Route::get('/competitions', [CompetitionController::class, 'index']);
    Route::get('/competitions/{id}', [CompetitionController::class, 'show']);

    //Daily Challenges
    Route::get('/daily-challenges', [DailyChallengeController::class, 'index']);
    Route::get('/daily-challenges/today', [DailyChallengeController::class, 'showByDate'])->name('daily-challenges.today');
    Route::get('/daily-challenges/date/{date}', [DailyChallengeController::class, 'showByDate']);
    Route::post('/daily-challenges', [DailyChallengeController::class, 'store']);
    Route::put('/daily-challenges/{id}', [DailyChallengeController::class, 'update']);
    Route::delete('/daily-challenges/{id}', [DailyChallengeController::class, 'destroy']);

    //Daily Challenge Submission
    Route::post('/daily-challenge-answer', [DailyChallengeSubmissionController::class, 'submitAnswer']);
    Route::get('/daily-challenge-answer/{userId}/state', [DailyChallengeSubmissionController::class, 'currentGameState']);

    //Daily Streak
    Route::get('/daily-streak/current', [DailyStreakController::class, 'current']);
    Route::post('/daily-streak/submit', [DailyStreakController::class, 'submit']);

    //Leaderboards
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    //Players
    Route::post('/players/search', [PlayerController::class, 'search']);

    //Quizzes
    Route::get('quizzes', [QuizController::class, 'index']);
    Route::get('questions', [QuizController::class, 'getQuestions']);
    Route::get('answers', [QuizController::class, 'getAnswers']);
    Route::get('questions-with-answers', [QuizController::class, 'getQuestionsWithAnswers']);
    Route::post('questions/bulk-add', [QuizController::class, 'bulkAddQuestions']);
    Route::post('quizzes/bulk-add', [QuizController::class, 'bulkAddQuizzes']);
    Route::get('quiz/random-unanswered', [QuizController::class, 'randomUnanswered']);

    //Quiz Submission
    Route::post('quiz/answer', [QuizSubmissionController::class, 'storeAnswer']);
    Route::get('score', [QuizSubmissionController::class, 'userTotalScore']);
    Route::get('quiz/results', [QuizSubmissionController::class, 'userQuizResults']);

    //Teams
    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);

    //Who Am I
    Route::get('who-am-i', [WhoAmIController::class, 'index']);
    Route::get('who-am-i/{id}', [WhoAmIController::class, 'show']);
    Route::post('who-am-i/bulk-add', [WhoAmIController::class, 'storeBulk']);

});
