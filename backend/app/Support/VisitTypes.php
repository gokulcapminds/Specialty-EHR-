<?php
namespace App\Support;

/**
 * The cardiology visit types - the ONE list used by appointments, the waiting list, walk-in encounters and the encounter form
 * (GET /api/visit-types feeds every dropdown; nothing else may hard-code <option>s). The number is the default appointment
 * length in minutes: it only pre-fills the booking popup, the user can still change it.
 */
class VisitTypes {
    public const LIST = [
        'New Patient Consultation'        => 45,
        'Follow-Up Visit'                 => 20,
        'Urgent Cardiology Visit'         => 30,
        'Post-Hospital Follow-Up'         => 30,
        'Post-Procedure Follow-Up'        => 30,
        'Medication Management'           => 20,
        'Preoperative Cardiac Evaluation' => 40,
        'Cardiology Consultation'         => 45,
    ];

    public const DEFAULT = 'Follow-Up Visit';

    /** Values saved before this list existed, mapped to their closest current type. Anything else is returned unchanged. */
    private const LEGACY = [
        'new patient'        => 'New Patient Consultation',
        'follow up'          => 'Follow-Up Visit',
        'follow-up'          => 'Follow-Up Visit',
        'cardiology consult' => 'Cardiology Consultation',
    ];

    public static function isValid(?string $name): bool {
        return $name !== null && array_key_exists($name, self::LIST);
    }

    public static function normalizeLegacy(?string $name): ?string {
        if ($name === null) return null;
        $key = mb_strtolower(trim($name));
        return self::LEGACY[$key] ?? $name;
    }

    /** [{name, minutes}] for the API. */
    public static function all(): array {
        $out = [];
        foreach (self::LIST as $name => $minutes) $out[] = ['name' => $name, 'minutes' => $minutes];
        return $out;
    }

    /**
     * Decides what to store for a visit type coming from a request.
     * $submitted null/'' -> $fallback. A value must be in the list, except that an UNCHANGED existing (legacy) value is kept
     * so old records stay editable. Returns [valueToStore, errorMessage|null].
     */
    public static function resolve($submitted, ?string $existing = null, string $fallback = self::DEFAULT): array {
        $submitted = is_string($submitted) ? trim($submitted) : '';
        if ($submitted === '') return [$existing !== null && $existing !== '' ? $existing : $fallback, null];
        if (self::isValid($submitted)) return [$submitted, null];
        if ($existing !== null && $submitted === $existing) return [$submitted, null];
        $mapped = self::normalizeLegacy($submitted);
        if (self::isValid($mapped)) return [$mapped, null];
        return [$submitted, 'Choose a visit type from the list: ' . implode(', ', array_keys(self::LIST)) . '.'];
    }
}
