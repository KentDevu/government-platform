<?php

/**
 * Minimal unit smoke test — confirms Pest runs and expect() works.
 *
 * Pest syntax used:
 *  • expect()    – Pest's fluent assertion API (replaces PHPUnit's $this->assert*).
 *  • toBeTrue()  – one of many expectation methods (toBe, toEqual, toContain, etc.).
 */
it('true is true', function (): void {
    expect(true)->toBeTrue();
});
