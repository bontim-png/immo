<?php
namespace App\Controllers;

use App\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request): void
    {
        $language = $request->getBody('language');
        $supported = config('app.supported_languages');
        
        if (in_array($language, $supported)) {
            $_SESSION['language'] = $language;
        }
        
        // Redirect back
        $referer = $_SERVER['HTTP_REFERER'] ?? route('home');
        $this->redirect($referer);
    }
}
