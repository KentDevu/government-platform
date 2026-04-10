# Pest Guide For This Project

## What Pest Is

Pest is a testing framework built on top of PHPUnit.

- PHPUnit is still the underlying engine.
- Pest gives you a cleaner syntax using closures like `it(...)` and `expect(...)`.
- You still get Laravel testing features (`actingAs`, `assertDatabaseHas`, etc.) when bound to Laravel `TestCase`.


## How Pest Works In This Repository

This project is configured so both Feature and Unit tests run with Laravel test context.

1. Global binding is defined in [tests/Pest.php](tests/Pest.php#L14):
   - `pest()->extend(Tests\TestCase::class)->in('Feature', 'Unit');`
2. The Laravel base test class is [tests/TestCase.php](tests/TestCase.php#L8):
   - Uses `RefreshDatabase`, so DB state is reset per test.
3. Test environment is configured in [phpunit.xml](phpunit.xml#L20):
   - `APP_ENV=testing`
   - `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`
   - `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, etc.


## How Pest Executes Tests (Sequence)

When you run Pest, this is the flow:

1. Autoload is initialized (`vendor/autoload.php`).
2. PHPUnit config is loaded from `phpunit.xml`.
3. Pest bootstrap is loaded from [tests/Pest.php](tests/Pest.php).
4. Test files in `tests/Feature` and `tests/Unit` are discovered.
5. Each `it(...)`/`test(...)` closure is executed.
6. Hooks like `beforeEach` and `afterEach` run around tests.
7. Assertions are evaluated.
8. Pest prints pass/fail output with test names, counts, and duration.


## How To Write Pest Tests

Pest style uses readable test sentences and expectation helpers.

```php
it('can update a profile', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/admin/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertRedirect(route('admin.profile'));
    expect($user->fresh()->name)->toBe('Updated Name');
});
```

Use the AAA pattern:

- Arrange: seed data, factories, fake services
- Act: execute request/job/class method
- Assert: verify status, DB rows, mail/job dispatch, etc.


## How To Run Pest

From project root:

1. Run all tests:
   - `./vendor/bin/pest`
2. Run all tests (compact output):
   - `./vendor/bin/pest --compact`
3. Run a single file:
   - `./vendor/bin/pest tests/Feature/AdminResourceCrudPestTest.php`
4. Run tests by name filter:
   - `./vendor/bin/pest --filter "staff can login"`
5. Stop on first failure:
   - `./vendor/bin/pest --bail`

You can also run through Laravel:

- `php artisan test`
- `php artisan test --compact`

In this repository, both commands run successfully.


## How Pest Tests "Correctness"

Pest validates behavior through assertions.

Common checks used in this project:

- HTTP assertions:
  - `assertOk()`, `assertRedirect(...)`, `assertForbidden()`, `assertNotFound()`
- Database assertions:
  - `assertDatabaseHas(...)`, `assertDatabaseMissing(...)`, `assertDatabaseCount(...)`
- Auth assertions:
  - `assertAuthenticatedAs(...)`, `assertGuest()`
- Mail/Queue assertions:
  - `Mail::assertSent(...)`, `Queue::assertPushed(...)`
- Value assertions:
  - `expect(...)->toBe(...)`, `expect(...)->toEqual(...)`, `expect(...)->toContain(...)`


## Discussion Script (Short)

If you need a quick explanation during presentation:

1. "Pest is a cleaner syntax layer on top of PHPUnit."
2. "In our project, Pest is bound to Laravel TestCase for Feature and Unit tests."
3. "Because TestCase uses RefreshDatabase, tests are isolated and repeatable."
4. "We run all tests with `./vendor/bin/pest` or `php artisan test`."
5. "Pest checks behavior using HTTP, database, auth, and mail/queue assertions."
