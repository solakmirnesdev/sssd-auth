<?php

use Solakmirnes\SssdAuth\Controllers\AuthController;

/**
 * API Routes
 *
 * This file defines the routes for the API endpoints. These endpoints
 * are used for authentication-related actions such as registration, login,
 * password reset, etc. All routes here are prefixed with /api/ to distinguish
 * them from web routes.
 */

// User registration
Flight::route('POST /api/register', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'register']);

// User login
Flight::route('POST /api/login', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'login']);

// Email verification
Flight::route('GET /api/verify', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'verifyEmail']);

// Password reset request
Flight::route('POST /api/forgot-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'forgotPassword']);

// Show password reset form
Flight::route('GET /api/reset-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'showResetForm']);

// Handle password reset
Flight::route('POST /api/reset-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'resetPassword']);
