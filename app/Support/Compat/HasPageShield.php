<?php

namespace BezhanSalleh\FilamentShield\Traits;

/**
 * Compatibility shim: swalbrun/filament-regex-import (<= 2.0.1) uses this
 * trait without declaring filament-shield as a dependency. Shield has been
 * removed from this project — panel access is controlled by the role based
 * {@link \App\Http\Middleware\EnsureUserIsAdmin} middleware instead, so the
 * trait can be an empty no-op.
 *
 * TODO Remove this file (and its composer classmap entry) once the package
 * drops its shield usage.
 */
trait HasPageShield {}
