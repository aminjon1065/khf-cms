<?php

use App\Support\PhoneNumber;

test('it canonicalizes formatted numbers to +digits', function (string $input, string $expected) {
    expect(PhoneNumber::normalize($input))->toBe($expected);
})->with([
    'spaced with country code' => ['+992 37 221-12-12', '+992372211212'],
    'parenthesised' => ['(992 37) 223-13-11', '+992372231311'],
    'bare 9-digit subscriber' => ['918 00 00 00', '+992918000000'],
    'double-zero international prefix' => ['00992 918 000 000', '+992918000000'],
    'already canonical' => ['+992918000000', '+992918000000'],
    'surrounding whitespace' => ['  +992 918 000 000  ', '+992918000000'],
]);

test('it leaves an empty or non-numeric value untouched', function () {
    expect(PhoneNumber::normalize(''))->toBe('')
        ->and(PhoneNumber::normalize('  '))->toBe('')
        ->and(PhoneNumber::normalize('нет'))->toBe('нет');
});
