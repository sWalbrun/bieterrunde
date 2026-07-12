<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Runs post-upload deployment tasks (database migrations + cache warming) from
 * the browser. The production host offers no CLI, SSH or cron — only FTP — so
 * these tasks cannot be run the usual way. Reachable only with the secret
 * {@see config('deploy.token')}; disabled entirely when that token is empty.
 *
 * Route caching is intentionally skipped: a closure route (/main/{any}) makes
 * `route:cache` fail, so we only cache config, views and events.
 */
class DeployController extends Controller
{
    /** Whitelisted commands run on deploy, in order. */
    private const COMMANDS = [
        ['migrate', ['--force' => true]],
        ['optimize:clear', []],
        ['config:cache', []],
        ['view:cache', []],
        ['event:cache', []],
    ];

    public function __invoke(Request $request, string $token): Response
    {
        $expected = (string) config('deploy.token');
        abort_if($expected === '' || ! hash_equals($expected, $token), 404);

        $log = '';
        foreach (self::COMMANDS as [$command, $parameters]) {
            try {
                $status = Artisan::call($command, $parameters);
                $log .= "$ php artisan $command  (exit {$status})\n".trim(Artisan::output())."\n\n";
            } catch (\Throwable $e) {
                $log .= "$ php artisan $command  FAILED: {$e->getMessage()}\n\n";
            }
        }

        return response($log."Done.\n", 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
