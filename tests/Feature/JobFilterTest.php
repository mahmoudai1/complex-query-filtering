<?php

test('returns jobs matching the complex filter', function () {
    $response = $this->getJson('/api/v1/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3');
    $response
    ->assertStatus(200)
    ->assertJson([
        'success' => true,
    ]);

    $responseData = $response->json();

    expect($responseData)->toHaveKey('data');
    expect(count($responseData['data']))->toBeGreaterThan(0);
});

test('no data found', function () {
    $response = $this->getJson('/api/v1/jobs?filter=(job_type=full-time-test AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=55');
    $response
    ->assertStatus(200)
    ->assertJson([
        'success' => true,
    ]);

    $responseData = $response->json();

    expect(count($responseData['data']))->toBe(0);
});

test('wrong query structure', function () {
    $response = $this->getJson('/api/v1/jobs?filter=(job_type_test=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3');
    $response
    ->assertStatus(500)
    ->assertJson([
        'success' => false,
    ]);
});
