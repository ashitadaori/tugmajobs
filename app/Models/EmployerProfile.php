<?php

namespace App\Models;

/**
 * DEPRECATED: This model is deprecated and kept only for backward compatibility.
 * Use the Employer model instead.
 *
 * This is an alias to the Employer model to maintain compatibility with legacy code.
 * The employer_profiles table has been dropped and all data migrated to employers table.
 *
 * @deprecated Use App\Models\Employer instead
 */
class EmployerProfile extends Employer
{
    /**
     * The table associated with the model.
     * Points to employers table for backward compatibility.
     *
     * @var string
     */
    protected $table = 'employers';

    /**
     * Constructor - Log deprecation warning
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Log deprecation notice (only in development)
        if (config('app.debug')) {
            \Log::warning('EmployerProfile model is deprecated. Use Employer model instead.', [
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ]);
        }
    }
}
