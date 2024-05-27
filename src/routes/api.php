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
/**
 * @api {post} /api/register Register a new user
 * @apiName RegisterUser
 * @apiGroup Auth
 *
 * @apiParam {String} full_name User's full name.
 * @apiParam {String} username Username for the account.
 * @apiParam {String} password Password for the account.
 * @apiParam {String} email User's email address.
 * @apiParam {String} phone_number User's phone number.
 *
 * @apiSuccess {String} message Success message.
 */
Flight::route('POST /api/register', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'register']);

// User login
/**
 * @api {post} /api/login Login a user
 * @apiName LoginUser
 * @apiGroup Auth
 *
 * @apiParam {String} username Username or email of the user.
 * @apiParam {String} password Password of the user.
 *
 * @apiSuccess {String} message Success message.
 * @apiSuccess {String} token JWT token for authentication.
 */
Flight::route('POST /api/login', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'login']);

// Email verification
/**
 * @api {get} /api/verify Verify a user's email
 * @apiName VerifyEmail
 * @apiGroup Auth
 *
 * @apiParam {Number} user User ID to verify.
 *
 * @apiSuccess {String} message Success message.
 */
Flight::route('GET /api/verify', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'verifyEmail']);

// Password reset request
/**
 * @api {post} /api/forgot-password Request a password reset
 * @apiName ForgotPassword
 * @apiGroup Auth
 *
 * @apiParam {String} email User's email address.
 *
 * @apiSuccess {String} message Success message.
 */
Flight::route('POST /api/forgot-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'forgotPassword']);

// Show password reset form
/**
 * @api {get} /api/reset-password Show password reset form
 * @apiName ShowResetForm
 * @apiGroup Auth
 *
 * @apiParam {String} token Reset token.
 *
 * @apiSuccess {String} form HTML form for password reset.
 */
Flight::route('GET /api/reset-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'showResetForm']);

// Handle password reset
/**
 * @api {post} /api/reset-password Reset the user's password
 * @apiName ResetPassword
 * @apiGroup Auth
 *
 * @apiParam {String} token Reset token.
 * @apiParam {String} new_password New password for the account.
 *
 * @apiSuccess {String} message Success message.
 */
Flight::route('POST /api/reset-password', ['Solakmirnes\SssdAuth\Controllers\AuthController', 'resetPassword']);
