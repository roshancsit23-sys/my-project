<?php
// SmartGov Market / HATIYA - Centralized Translation Engine
// File: includes/i18n.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user requested a language change via GET
if (isset($_GET['lang'])) {
    $reqLang = strtolower(trim($_GET['lang']));
    if (in_array($reqLang, ['en', 'ne'], true)) {
        $_SESSION['lang'] = $reqLang;
        setcookie('smartgov_lang', $reqLang, time() + (86400 * 30), '/');
    }
}

// Resolve current active language
function currentLang() {
    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'ne'], true)) {
        return $_SESSION['lang'];
    }
    if (!empty($_COOKIE['smartgov_lang']) && in_array($_COOKIE['smartgov_lang'], ['en', 'ne'], true)) {
        $_SESSION['lang'] = $_COOKIE['smartgov_lang'];
        return $_COOKIE['smartgov_lang'];
    }
    return 'en';
}

function isNepali() {
    return currentLang() === 'ne';
}

// Load dictionaries statically
function getTranslationDictionary($lang) {
    static $dictionaries = [];
    if (!isset($dictionaries[$lang])) {
        $file = __DIR__ . "/../lang/{$lang}.php";
        if (file_exists($file)) {
            $dictionaries[$lang] = require $file;
        } else {
            $dictionaries[$lang] = [];
        }
    }
    return $dictionaries[$lang];
}

// Global translation helper
function __($key, $default = null) {
    $lang = currentLang();
    $dict = getTranslationDictionary($lang);
    if (isset($dict[$key])) {
        return $dict[$key];
    }
    // Fallback to English dictionary
    if ($lang !== 'en') {
        $enDict = getTranslationDictionary('en');
        if (isset($enDict[$key])) {
            return $enDict[$key];
        }
    }
    return $default !== null ? $default : $key;
}

// Translate status badge dynamically
function transStatus($status) {
    $normalized = strtolower(str_replace(' ', '_', trim($status)));
    $key = 'status_' . $normalized;
    return __($key, $status);
}
