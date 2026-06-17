<?php

namespace App\Traits;

/**
 * Provides department-code variant resolution.
 *
 * Old staff records may store department codes like "IT", "BSBA", "EDUC", etc.
 * New student registrations use "BSIT", "CBAE", "CTE", etc.
 * Any given department value should be expanded to ALL equivalent codes when
 * building WHERE clauses, so that records created under either naming convention
 * are always found.
 */
trait DeptVariantHelper
{
    /**
     * Return every department-code alias that belongs to the same group as $dept.
     *
     * @param  string  $dept  Any old or new department code.
     * @return array          All equivalent codes (never empty – always contains $dept).
     */
    protected function getDeptVariants(string $dept): array
    {
        $dept = strtoupper(trim($dept));

        $groups = [
            ['BSIT', 'IT', 'ENGINEERING'],
            ['CBAE', 'BSBA', 'PSYCHOLOGY', 'ACCOUNTANCY'],
            ['CTE',  'EDUC'],
            ['CHTM', 'NURSING'],
            ['CRIM'],
            ['SHS'],
        ];

        foreach ($groups as $group) {
            if (in_array($dept, $group, true)) {
                return $group;
            }
        }

        // Unknown code – return as-is so queries still work
        return [$dept];
    }

    /**
     * Check whether two department codes belong to the same equivalence group.
     */
    protected function sameDept(string $a, string $b): bool
    {
        return !empty(array_intersect(
            $this->getDeptVariants($a),
            $this->getDeptVariants($b)
        ));
    }
}
