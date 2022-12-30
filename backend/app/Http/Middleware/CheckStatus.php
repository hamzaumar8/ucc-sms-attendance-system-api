<?php

namespace App\Http\Middleware;

use App\Models\Semester;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $semester  = Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
        $user = auth()->user;
        if ($semester) {
            if ($semester->id !== $user->semester_id) {
                $user->update([
                    'semester_id' => $semester->id,
                ]);
            }
        } else {
            $user->update([
                'semester_id' => null,
            ]);
        }
        return $next($request);
    }
}