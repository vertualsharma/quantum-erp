<?php

use App\Exports\StudentTemplateExport;
use App\Http\Controllers\PrintResultAutoController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});

Route::get('student-template/download', function () {
    return Excel::download(new StudentTemplateExport(), 'student-import-template.xlsx');
})->name('student-template.download');

Route::middleware(['web'])->group(function () {
    Route::get('print/result/single/{studentResultId}', [PrintResultAutoController::class, 'single'])->name('print.result.single');
    Route::get('print/result/class/{examClassSetupId}', [PrintResultAutoController::class, 'wholeClass'])->name('print.result.class');
});
