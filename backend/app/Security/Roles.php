<?php
namespace App\Security;

/**
 * The one place that says which role may do what. Controllers call Roles::enforce(Roles::CLINICAL) instead of
 * typing role names, so a role can be added/removed/changed here and nowhere else.
 *
 * Staff roles (users.role ENUM, minus 'Patient' which is reserved for a future portal):
 *   Super Admin, Doctor, Nurse, Receptionist, Billing Staff.
 * (The Therapist role was removed on request.) There are deliberately no per-user permission overrides:
 * a person's access is exactly their role's access.
 */
class Roles {
    public const SUPER_ADMIN  = 'Super Admin';
    public const DOCTOR       = 'Doctor';
    public const NURSE        = 'Nurse';
    public const RECEPTIONIST = 'Receptionist';
    public const BILLING      = 'Billing Staff';

    /** Every staff role (anyone who may use the application). */
    public const ALL_STAFF = [self::SUPER_ADMIN, self::DOCTOR, self::NURSE, self::RECEPTIONIST, self::BILLING];

    /**
     * Facility-based data isolation (Oct 2026): every role, including Super Admin, only sees PHI/workflow data
     * (patients, appointments, encounters, billing, referrals, recalls, messages, documents, ...) for their own
     * facility - there is no role-based exemption, so this is NOT a role-group constant like the ones above.
     * Controllers scope these reads/writes with `AND <patients-or-users>.facility_id = ?` bound to
     * `$_SESSION['facility_id']` (re-read fresh every request by AuthenticationMiddleware, same as user_role).
     * The only screens that stay enterprise-wide are the admin ones already gated by Roles::ADMIN: Facility
     * Management, User Management, Roles & Permissions, Audit Log, and Specialty Management (specialties
     * themselves are a shared catalog, not facility data - see facility_specialties).
     */

    /** System administration: users, facilities, specialties, settings, roles, audit log, deleting a patient. */
    public const ADMIN = [self::SUPER_ADMIN];

    /** Licensed providers: sign/lock notes, prescribe, place orders, sign off results, delete a draft note. */
    public const PROVIDER = [self::SUPER_ADMIN, self::DOCTOR];

    /** Anyone who may read/write clinical content (notes, documents, allergies, problems, medications, orders, results). */
    public const CLINICAL = [self::SUPER_ADMIN, self::DOCTOR, self::NURSE];

    /** Scheduling and care coordination: appointments, referrals, recalls, telehealth sessions. */
    public const CARE_COORDINATION = [self::SUPER_ADMIN, self::DOCTOR, self::NURSE, self::RECEPTIONIST];

    /**
     * Roles that practise a clinical specialty, so a user with one of them must have a Primary Specialty.
     * Super Admin, Receptionist and Billing Staff may leave it blank (they then open the default specialty workspace at login).
     * The wizard mirrors this in staffSpecialtyRequired() (app.js); this list is the one that is enforced.
     */
    public const SPECIALTY_REQUIRED = [self::DOCTOR, self::NURSE];

    /** Who may create/change invoices, claims and payments. */
    public const BILLING_TEAM = [self::SUPER_ADMIN, self::BILLING];

    /** Who may look at invoices and claims (providers can see what their visits were billed as). */
    public const BILLING_VIEW = [self::SUPER_ADMIN, self::DOCTOR, self::BILLING];

    /**
     * What each role may do, per area - the ONE definition behind both the read-only "Roles & Permissions" tab and the
     * `permissions` object `GET /api/me` sends to the UI (button/switch gating). Each action maps to the same role-group
     * constant the controller enforces with, so changing a rule means editing that constant (or the controller's
     * enforce() call and the entry here together). An empty list = nobody (action does not exist / not allowed).
     * Keys `patients`, `admin_users`, `admin_roles` are read by app.js; keep them.
     */
    public const AREAS = [
        'dashboard'        => ['label' => 'Dashboard',                 'group' => 'General',        'view' => self::ALL_STAFF, 'create' => [], 'edit' => [], 'delete' => []],
        'messaging'        => ['label' => 'Messages & notifications', 'group' => 'General',        'view' => self::ALL_STAFF, 'create' => self::ALL_STAFF, 'edit' => [], 'delete' => self::ALL_STAFF],
        'calendar'         => ['label' => 'Calendar & appointments',   'group' => 'Front desk',     'view' => self::ALL_STAFF, 'create' => self::CARE_COORDINATION, 'edit' => self::CARE_COORDINATION, 'delete' => self::CARE_COORDINATION],
        'telehealth'       => ['label' => 'Telehealth',                'group' => 'Front desk',     'view' => self::ALL_STAFF, 'create' => self::CARE_COORDINATION, 'edit' => self::CARE_COORDINATION, 'delete' => []],
        'patients'         => ['label' => 'Patients (chart & demographics)', 'group' => 'Front desk', 'view' => self::ALL_STAFF, 'create' => self::CARE_COORDINATION, 'edit' => self::ALL_STAFF, 'delete' => self::ADMIN],
        'referrals'        => ['label' => 'Referrals',                 'group' => 'Front desk',     'view' => self::CARE_COORDINATION, 'create' => self::CARE_COORDINATION, 'edit' => self::CARE_COORDINATION, 'delete' => self::CARE_COORDINATION],
        'recalls'          => ['label' => 'Recalls',                   'group' => 'Front desk',     'view' => self::CARE_COORDINATION, 'create' => self::CARE_COORDINATION, 'edit' => self::CARE_COORDINATION, 'delete' => self::CARE_COORDINATION],
        'encounters'       => ['label' => 'Encounters (clinical notes)', 'group' => 'Clinical',     'view' => self::CLINICAL, 'create' => self::CLINICAL, 'edit' => self::CLINICAL, 'delete' => self::PROVIDER],
        'encounter_sign'   => ['label' => 'Sign, lock & addendum',     'group' => 'Clinical',       'view' => [], 'create' => [], 'edit' => self::PROVIDER, 'delete' => []],
        'orders'           => ['label' => 'Orders & lab results',      'group' => 'Clinical',       'view' => self::CLINICAL, 'create' => self::PROVIDER, 'edit' => self::CLINICAL, 'delete' => []],
        'medications'      => ['label' => 'Medications',               'group' => 'Clinical',       'view' => self::CLINICAL, 'create' => self::PROVIDER, 'edit' => self::PROVIDER, 'delete' => []],
        'allergies_problems' => ['label' => 'Allergies & diagnoses',   'group' => 'Clinical',       'view' => self::CLINICAL, 'create' => self::CLINICAL, 'edit' => self::CLINICAL, 'delete' => []],
        'documents'        => ['label' => 'Documents',                 'group' => 'Clinical',       'view' => self::CLINICAL, 'create' => self::CLINICAL, 'edit' => [], 'delete' => self::CLINICAL],
        'billing'          => ['label' => 'Billing (invoices, claims, payments)', 'group' => 'Billing', 'view' => self::BILLING_VIEW, 'create' => self::BILLING_TEAM, 'edit' => self::BILLING_TEAM, 'delete' => self::BILLING_TEAM],
        'admin_facility'   => ['label' => 'Facilities',                'group' => 'Administration', 'view' => self::ALL_STAFF, 'create' => self::ADMIN, 'edit' => self::ADMIN, 'delete' => self::ADMIN],
        'admin_specialties' => ['label' => 'Specialties',              'group' => 'Administration', 'view' => self::ALL_STAFF, 'create' => self::ADMIN, 'edit' => self::ADMIN, 'delete' => self::ADMIN],
        'admin_users'      => ['label' => 'Users',                     'group' => 'Administration', 'view' => self::ADMIN, 'create' => self::ADMIN, 'edit' => self::ADMIN, 'delete' => self::ADMIN],
        'admin_roles'      => ['label' => 'Roles & permissions',       'group' => 'Administration', 'view' => self::ADMIN, 'create' => [], 'edit' => [], 'delete' => []],
        'settings'         => ['label' => 'Settings',                  'group' => 'Administration', 'view' => self::ADMIN, 'create' => [], 'edit' => self::ADMIN, 'delete' => []],
        'audit'            => ['label' => 'Audit log',                 'group' => 'Administration', 'view' => self::ADMIN, 'create' => [], 'edit' => [], 'delete' => []],
    ];

    public const ACTIONS = ['view', 'create', 'edit', 'delete'];

    /** Built-in roles a custom role may be based on (never Super Admin: a custom role can only narrow, and admin areas stay admin-only). */
    public const CUSTOM_BASE_ROLES = [self::DOCTOR, self::NURSE, self::RECEPTIONIST, self::BILLING];

    /**
     * `{ area: { view, create, edit, delete } }` of booleans for one role. With a custom-role matrix the result is
     * the base role's access AND the ticks (a custom role can only narrow its base role).
     */
    public static function permissionsFor(string $role, ?array $customMatrix = null): array {
        $out = [];
        foreach (self::AREAS as $key => $area) {
            foreach (self::ACTIONS as $action) {
                $ok = in_array($role, $area[$action], true);
                if ($ok && $customMatrix !== null) $ok = !empty($customMatrix[$key][$action]);
                $out[$key][$action] = $ok;
            }
        }
        return $out;
    }

    /** True when this cell can never be ticked for a custom role (only Super Admin has it). */
    public static function isLockedCell(string $area, string $action): bool {
        $groups = self::AREAS[$area][$action] ?? [];
        return $groups === self::ADMIN || $groups === [];
    }

    /**
     * Cleans a posted matrix into a full {area:{view,create,edit,delete}} of booleans: unknown areas/actions dropped,
     * anything beyond the base role (or locked) forced off, and create/edit/delete imply view.
     */
    public static function sanitizeMatrix($input, string $baseRole): array {
        $input = is_array($input) ? $input : [];
        $out = [];
        foreach (self::AREAS as $key => $area) {
            $cell = [];
            foreach (self::ACTIONS as $action) {
                $cell[$action] = !empty($input[$key][$action])
                    && !self::isLockedCell($key, $action)
                    && in_array($baseRole, $area[$action], true);
            }
            if (($cell['create'] || $cell['edit'] || $cell['delete']) && in_array($baseRole, $area['view'], true)) $cell['view'] = true;
            $out[$key] = $cell;
        }
        return $out;
    }

    /** The logged-in user's custom-role matrix (set per request by AuthenticationMiddleware), or null for a built-in role. */
    public static function sessionMatrix(): ?array {
        return isset($_SESSION['custom_perms']) && is_array($_SESSION['custom_perms']) ? $_SESSION['custom_perms'] : null;
    }

    /** The current user's effective permissions (built-in role, or base role narrowed by their custom role). */
    public static function currentPermissions(): array {
        return self::permissionsFor(self::current(), self::sessionMatrix());
    }

    /** Custom-role check for one area/action: base role must allow it AND the role's tick must be on. */
    public static function customAllows(string $area, string $action): bool {
        $m = self::sessionMatrix();
        if ($m === null) return true;                       // built-in role: the controllers decide
        return !empty($m[$area][$action]) && in_array(self::current(), self::AREAS[$area][$action] ?? [], true);
    }

    /** The whole table for the read-only Roles & Permissions tab: areas (with labels) and each role's permissions. */
    public static function matrix(): array {
        $areas = [];
        foreach (self::AREAS as $key => $area) {
            $areas[] = ['key' => $key, 'label' => $area['label'], 'group' => $area['group']];
        }
        $roles = [];
        foreach (self::ALL_STAFF as $role) {
            $roles[$role] = self::permissionsFor($role);
        }
        return ['areas' => $areas, 'roles' => $roles];
    }

    public static function current(): string {
        return (string)($_SESSION['user_role'] ?? '');
    }

    public static function is(array $allowed): bool {
        return in_array(self::current(), $allowed, true);
    }

    /**
     * Stops the request with 401 (no session) or 403 (wrong role) as JSON.
     * Use at the top of every controller method that returns or changes protected data.
     */
    public static function enforce(array $allowed): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
        if (!self::is($allowed)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden: your role does not allow this.']);
            exit();
        }
    }
}
