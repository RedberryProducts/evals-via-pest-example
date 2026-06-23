<?php

use App\Ai\Agents\SupportPolicyAgent;

// --- Test 1: String Content Assertions ---
test('string content assertions', function () {
    fakeAgentResponseIfLiveDisabled(SupportPolicyAgent::class, [
        'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase. Refunds typically take 5-10 business days to appear in your bank account.',
    ]);

    evaluate(SupportPolicyAgent::class)
        ->prompt('Tell me about the refund policy')
        ->assertContains('refund')
        ->assertContains(['refund', '14 days'])
        ->assertContainsAny(['refund', 'support', 'hours'])
        ->assertNotContains('Python')
        ->assertMatches('/14 days/i')
        ->assertString()
        ->assertNotEmpty();
})->group('deterministic');

// --- Test 2: Length Assertions ---
test('length assertions', function () {
    fakeAgentResponseIfLiveDisabled(SupportPolicyAgent::class, [
        'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
    ]);

    evaluate(SupportPolicyAgent::class)
        ->prompt('What are our operating hours?')
        ->assertLengthGreaterThan(10)
        ->assertLengthLessThan(100)
        ->assertLengthBetween(10, 100);
})->group('deterministic');

// --- Test 3: JSON Content Assertions ---
test('json assertions', function () {
    fakeAgentResponseIfLiveDisabled(SupportPolicyAgent::class, [
        json_encode([
            'answer' => 'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
            'category' => 'general',
            'status' => 'active',
        ]),
    ]);

    evaluate(SupportPolicyAgent::class)
        ->prompt('Provide a JSON response with our operating hours: general category, active status.')
        ->assertJson()
        ->assertJsonPath('category', 'general')
        ->assertJsonStructure(['answer', 'category', 'status']);
})->group('deterministic');

// --- Test 4: Equality and Exact Assertions ---
test('equality and exact assertions', function () {
    fakeAgentResponseIfLiveDisabled(SupportPolicyAgent::class, [
        '9 AM to 5 PM EST',
    ]);

    evaluate(SupportPolicyAgent::class)
        ->prompt('What are our exact support hours?')
        ->assertEquals('9 AM to 5 PM EST')
        ->toBe('9 AM to 5 PM EST');
})->group('deterministic');
