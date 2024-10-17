<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
        $this->mapMeetingApiRoutes();
        $this->mapTaskApiRoutes();
        $this->mapChatGroupApiRoutes();
        $this->mapDocumentApiRoutes();
        $this->mapDecisionApiRoutes();
        $this->mapUserApiRoutes();
        $this->mapOrganizationsAPiRoutes();
        $this->mapAdminApiRoutes();
        $this->mapMetaDateApiRoutes();
        $this->mapFileSystemApiRoutes();
        $this->mapCommitteeDashboardApiRoutes();
        $this->mapWebhookRoutes();
        $this->mapSignatureApiRoutes();
        $this->mapApprovalApiRoutes();
        $this->mapRequestApiRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the " meetings api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapMeetingApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/meeting-api.php');
        });
    }

    /**
     * Define the " tasks api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTaskApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/task-api.php');
        });
    }

    /**
     * Define the " chat groups api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapChatGroupApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/chat-group-api.php');
        });
    }

    /**
     * Define the "document api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapDocumentApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/document-api.php');
        });
    }

    /**
     * Define the "decision api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapDecisionApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/decision-api.php');
        });
    }

    /**
     * Define the "users api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapUserApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/user-api.php');
        });
    }

    protected function mapOrganizationsAPiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/organization-api.php');
        });
    }

    /**
     * Define the "admin api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/admin-api.php');
        });
    }

    /**
     * Define the "metadata api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapMetaDateApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/metadata-api.php');
        });
    }

    /**
     * Define the "file system api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapFileSystemApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/file-system-api.php');
        });
    }

    /**
     * Define the "committee dashboard api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapCommitteeDashboardApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/committee-dashboard-api.php');
        });
    }

    /**
     * Define the "file system api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapWebhookRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/stc-webhook.php');
        });
    }

    /**
     * Define the "signature integration api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapSignatureApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/signature-api.php');
        });
    }

        /**
     * Define the "metadata api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApprovalApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/approval-api.php');
        });
    }
    /**
     * Define the "request api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapRequestApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/request-api.php');
        });
    }

}
