<?php
namespace App\Security;

/**
 * Maps an API route to the permission area + action it needs, so a user on a CUSTOM role can be checked centrally
 * (AuthenticationMiddleware) without touching every controller. Built-in roles are still enforced by the controllers;
 * a custom role can only narrow its base role, so both checks must pass.
 *
 * Action defaults from the HTTP method (GET view, POST create, PUT/PATCH edit, DELETE delete); RULES override it where a
 * POST really edits something (sign, resolve, accept, void...). resolve() returns NEUTRAL for routes every signed-in
 * user needs, or null for a route nobody mapped - which is DENIED for custom-role users (fail closed): when you add a
 * route, add it here.
 */
class RouteAreas {
    public const NEUTRAL = 'neutral';

    /** [regex on the path after /api/, area ('neutral' = always allowed), action override or null] - first match wins. */
    private const RULES = [
        ['#^(me|logout|auth/change-password|notifications|intake/notifications|providers|patients/providers|visit-types)$#', self::NEUTRAL, null],
        ['#^patient/\d+/dashboard$#', 'patients', 'view'],
        ['#^(patients?|intake)(/|$)#', 'patients', null],
        ['#^(appointments?|provider-blocks)(/|$)#', 'calendar', null],
        ['#^dashboard/#', 'dashboard', 'view'],
        ['#^clinical/notes/\d+/(sign|addendum)$#', 'encounter_sign', 'edit'],
        ['#^clinical/#', 'encounters', null],
        ['#^encounters/#', 'encounters', null],
        ['#^documents(/|$)#', 'documents', null],
        ['#^imaging(/|$)#', 'documents', null],
        ['#^referrals/\d+(/(accept|reject))?$#', 'referrals', 'POST:edit'],
        ['#^referrals(/|$)#', 'referrals', null],
        ['#^recalls/\d+(/.*)?$#', 'recalls', 'POST:edit'],
        ['#^recalls(/|$)#', 'recalls', null],
        ['#^orders/\d+/(results|review)$#', 'orders', 'POST:edit'],
        ['#^orders(/|$)#', 'orders', null],
        ['#^medications/\d+/refill$#', 'medications', 'POST:edit'],
        ['#^medications(/|$)#', 'medications', null],
        ['#^(problems|allergies)/\d+/(resolve|reactivate)$#', 'allergies_problems', 'POST:edit'],
        ['#^(problems|allergies)(/|$)#', 'allergies_problems', null],
        ['#^messages(/|$)#', 'messaging', null],
        ['#^billing/payment/\d+/void$#', 'billing', 'edit'],
        ['#^billing/claims/\d+/.+#', 'billing', 'POST:edit'],
        ['#^billing(/|$)#', 'billing', null],
        ['#^telehealth/session/\d+/resend$#', 'telehealth', 'POST:edit'],
        ['#^telehealth(/|$)#', 'telehealth', null],
        ['#^users?(/|$)#', 'admin_users', null],
        ['#^facilities(/|$)#', 'admin_facility', null],
        ['#^specialties(/|$)#', 'admin_specialties', null],
        ['#^settings(/|$)#', 'settings', null],
        ['#^roles(/|$)#', 'admin_roles', null],
        ['#^reports/audit#', 'audit', 'view'],
        ['#^reports/(summary|financial|clinical|operations|users|specialties|export)#', self::NEUTRAL, null],
    ];

    private static function defaultAction(string $method): string {
        switch (strtoupper($method)) {
            case 'POST': return 'create';
            case 'PUT': case 'PATCH': return 'edit';
            case 'DELETE': return 'delete';
            default: return 'view';
        }
    }

    /** @return array{area:string,action:string}|string|null  array = needs that area/action, NEUTRAL, or null = unmapped */
    public static function resolve(string $method, string $path) {
        $path = preg_replace('#^.*?/api/#', '', $path);
        $path = trim(strtok($path, '?'), '/');
        foreach (self::RULES as [$regex, $area, $override]) {
            if (!preg_match($regex, $path)) continue;
            if ($area === self::NEUTRAL) return self::NEUTRAL;
            $action = self::defaultAction($method);
            if ($override !== null) {
                if (strpos($override, ':') !== false) {            // 'POST:edit' = only for that method
                    [$m, $a] = explode(':', $override, 2);
                    if (strtoupper($method) === $m) $action = $a;
                } else {
                    $action = $override;
                }
            }
            return ['area' => $area, 'action' => $action];
        }
        return null;
    }
}
