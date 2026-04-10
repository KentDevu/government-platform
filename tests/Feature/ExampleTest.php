<?php

/**
 * Smoke test — verifies the app boots and the homepage returns HTTP 200.
 *
 * Pest syntax used:
 *  • it()   – defines a single test case as a closure (no class needed).
 *  • $this  – auto-bound to the Laravel TestCase, so HTTP helpers like
 *             ->get() and ->assertStatus() work out of the box.
 */
it('the application returns a successful response', function (): void {
    // Send a GET request to the root URL and assert a 200 OK response.
    $response = $this->get('/');

    $response->assertStatus(200);
});
