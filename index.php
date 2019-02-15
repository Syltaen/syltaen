<?php

namespace Syltaen;

// ==================================================
// > SINGLES
// ==================================================
Route::is("single", "SingleController::render");

// ==================================================
// > SEARCH
// ==================================================
Route::is("search", "PageController::search", ["search" => get_search_query(false)]);

// ==================================================
// > API
// ==================================================
Route::custom("api", "ApiController", ["method", "target", "mode"]);

// ==================================================
// > NINJA FORM PREVIEW
// ==================================================
Route::query("nf_preview_form", "PageController::ninjaFormPreview");

// ==================================================
// > PAGES
// ==================================================
Route::is(["home", "front_page"], "PageController::home");
Route::is("page", "PageController::default");

// ==================================================
// > 404
// ==================================================
Route::is("404", "PageController::error404");