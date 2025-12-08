<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartmentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $userDept = auth()->user()->department->type(); // misal "marketing", "engineering", dll

        $routeName = $request->route()->getName();

        $routeParts = explode('.', $routeName);

        $group = $routeParts[0] ?? null;

        // kalau group adalah departemen (marketing, management, engineering) baru dicek
        $departments = ['marketing', 'engineering', 'management'];

        if (in_array($group, $departments) && $group !== $userDept) {
            abort(403, 'Access Unauthorized');
        }

        return $next($request);
    }
}
