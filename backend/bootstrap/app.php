<?php

use App\Console\Commands\DbReportIndexesCommand;
use App\Console\Commands\ExpireListingsCommand;
use App\Console\Commands\GeocodeListingsCommand;
use App\Console\Commands\PurgeAuditLogsCommand;
use App\Console\Commands\PirOfflineEvaluateCommand;
use App\Console\Commands\PurgeChatAttachmentsCommand;
use App\Console\Commands\PurgeExpiredKycDocumentsCommand;
use App\Console\Commands\PurgeExpiredTrustedDevicesCommand;
use App\Console\Commands\PurgeNotificationsCommand;
use App\Console\Commands\RecomputeBadgesCommand;
use App\Console\Commands\SavedSearchMatchCommand;
use App\Console\Commands\SearchListingsReindexCommand;
use App\Console\Commands\SearchListingsSyncMissingCommand;
use App\Console\Commands\VerifyBackupCommand;
use App\Http\Middleware\ChatAttachmentRateLimit;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Services\SentryReporter;
use App\Services\StructuredLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\ValidatePathEncoding;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents(discover: false)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(__DIR__.'/../routes/channels.php', [
        'middleware' => ['web', 'auth:sanctum'],
    ])
    ->withCommands([
        DbReportIndexesCommand::class,
        PirOfflineEvaluateCommand::class,
        ExpireListingsCommand::class,
        \App\Console\Commands\SendNotificationDigestCommand::class,
        GeocodeListingsCommand::class,
        PurgeAuditLogsCommand::class,
        PurgeChatAttachmentsCommand::class,
        PurgeExpiredKycDocumentsCommand::class,
        PurgeExpiredTrustedDevicesCommand::class,
        PurgeNotificationsCommand::class,
        RecomputeBadgesCommand::class,
        SavedSearchMatchCommand::class,
        SearchListingsReindexCommand::class,
        SearchListingsSyncMissingCommand::class,
        VerifyBackupCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('listings:expire')->dailyAt('02:00');
        $schedule->command('badges:recompute')->dailyAt('03:00');
        $schedule->command('notifications:digest --frequency=daily')->dailyAt('09:00');
        $schedule->command('notifications:digest --frequency=weekly')->weeklyOn(1, '09:00'); // Monday
        $schedule->command('saved-searches:match')->everyFifteenMinutes();
        $schedule->command('kyc:purge-expired')->dailyAt('04:00');
        $schedule->command('trusted-devices:purge')->dailyAt('04:30');
        // Data retention / GDPR cleanup
        $schedule->command('attachments:purge-old')->dailyAt('05:00');
        $schedule->command('audit-logs:purge-old')->dailyAt('05:15');
        $schedule->command('notifications:purge-old')->dailyAt('05:30');
        // Backup health verification
        $schedule->command('backup:verify')->dailyAt('06:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            ValidatePathEncoding::class,
            TrustProxies::class,
            RequestIdMiddleware::class,
            SecurityHeadersMiddleware::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        $middleware->statefulApi();
        $middleware->throttleApi();

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'chat_attachments' => ChatAttachmentRateLimit::class,
            'mfa' => \App\Http\Middleware\EnsureMfaVerified::class,
            'admin_mfa' => \App\Http\Middleware\RequireMfaForAdmin::class,
            'session_activity' => \App\Http\Middleware\SessionActivity::class,
            'sentry_user' => \App\Http\Middleware\SetSentryUserContext::class,
        ]);

        $middleware->appendToGroup('api', \App\Http\Middleware\SetSentryUserContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            // Browser navigating directly to an API URL — redirect to the app with an error notification.
            if ($request->is('api/*') && str_contains($request->header('Accept', ''), 'text/html')) {
                $appUrl = rtrim(config('app.frontend_url', config('app.url', '')), '/');
                $referer = $request->headers->get('referer', '');

                if ($referer && parse_url($referer, PHP_URL_HOST) === parse_url($appUrl, PHP_URL_HOST)) {
                    $base = $referer;
                } else {
                    $base = $appUrl.'/';
                }

                $separator = str_contains($base, '?') ? '&' : '?';

                return redirect($base.$separator.'error=access_denied');
            }

            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->report(function (Throwable $e) {
            if ($e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof HttpResponseException) {
                return;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            if ($status < 500) {
                return;
            }

            app(StructuredLogger::class)->error('unhandled_exception', [
                'status' => $status,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'url' => request()?->fullUrl(),
                'request_id' => request()?->attributes->get('request_id') ?? request()?->header('X-Request-Id'),
            ]);

            app(SentryReporter::class)->captureException($e, [
                'status' => $status,
                'flow' => 'http_exception',
            ]);
        });
    })->create();
