<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobView extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'ip_address',
        'user_agent',
        'device_type',
        'referrer'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public static function recordView($job, $request)
    {
        try {
            return static::create([
                'job_id' => $job->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => static::getDeviceType($request->userAgent()),
                'referrer' => $request->header('referer')
            ]);
        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::warning('Failed to record job view: ' . $e->getMessage(), [
                'job_id' => $job->id,
                'ip_address' => $request->ip(),
                'referrer_length' => strlen($request->header('referer') ?? ''),
                'user_agent_length' => strlen($request->userAgent() ?? '')
            ]);
            
            // Return null or a default object to prevent breaking the flow
            return null;
        }
    }

    private static function getDeviceType($userAgent)
    {
        $deviceTypes = [
            'Mobile' => '/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|boost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i',
            'Tablet' => '/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/i'
        ];

        foreach ($deviceTypes as $type => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $type;
            }
        }

        return 'Desktop';
    }
} 