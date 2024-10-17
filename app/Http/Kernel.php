<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Http\Middleware\HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            /*      'throttle:60,1', */
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'jwt.customAuth' => \App\Http\Middleware\CustomGetUserFromToken::class,
        'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        'userDeleted' => \App\Http\Middleware\CheckUserDeleted::class,
        'userActivated' => \App\Http\Middleware\CheckUserActivated::class,
        'organizationAccess' => \App\Http\Middleware\CheckOrganizationAccess::class,
        'meetingAccess' => \App\Http\Middleware\checkMeetingAccess::class,
        'voteAccess' => \App\Http\Middleware\checkVoteAccess::class,
        'organizationCompletedProfile' => \App\Http\Middleware\CheckOrganizationCompletedProfile::class,
        'organizationExpirationLicense' => \App\Http\Middleware\CheckOrganizationLicenseExpiration::class,
        'checkNumberOfFailedLoginAttempt' => \App\Http\Middleware\CheckNumberOfFailedLoginAttempt::class,
        'checkAuthorization' => \App\Http\Middleware\CheckAuthorization::class,
        'storageAccessCheck' => \App\Http\Middleware\StorageAccessCheck::class,
        'storageLimit' => \App\Http\Middleware\StorageLimit::class,
        'stcWebhookSecurity' => \App\Http\Middleware\CheckStcWebhookSecurity::class,
        'checkIsSigned' => \App\Http\Middleware\CheckIsSigned::class,
    ];
}
