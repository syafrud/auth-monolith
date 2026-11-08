<?php

use Carbon\CarbonImmutable;
use Modules\Library\Circulation\ValueObjects\Fine;
use Modules\Shared\ValueObjects\Isbn;
use Modules\Shared\ValueObjects\Money;

test('isbn accepts valid isbn 10 and isbn 13 and normalizes hyphens', function (string $input, string $expected) {
    expect((new Isbn($input))->getValue())->toBe($expected);
})->with([
    ['978-979-3062-79-2', '9789793062792'],
    ['978 602 03 0064 1', '9786020300641'],
    ['0-306-40615-2', '0306406152'],
    ['080442957x', '080442957X'],
]);

test('isbn rejects invalid format or checksum', function (string $input) {
    expect(fn () => new Isbn($input))->toThrow(InvalidArgumentException::class, 'Format ISBN tidak valid');
    expect(Isbn::isValid($input))->toBeFalse();
})->with(['12345', '9789793062791', '1234567890123', 'abcdefghij', '0306406153']);

test('isbn equality compares normalized values', function () {
    expect((new Isbn('978-979-3062-79-2'))->equals(new Isbn('9789793062792')))->toBeTrue();
});

test('fine is zero when returned on or before the due date', function () {
    $due = CarbonImmutable::parse('2026-11-10 09:00');

    $onTime = Fine::calculate($due, $due->setTime(23, 59), new Money(1000));
    $early = Fine::calculate($due, $due->subDays(2), new Money(1000));

    expect($onTime->getDaysLate())->toBe(0)
        ->and($onTime->isCharged())->toBeFalse()
        ->and($early->getAmount()->getAmount())->toBe(0.0);
});

test('fine is calculated automatically per late day', function () {
    $fine = Fine::calculate(
        CarbonImmutable::parse('2026-11-10 09:00'),
        CarbonImmutable::parse('2026-11-13 08:00'),
        new Money(1000),
    );

    expect($fine->getDaysLate())->toBe(3)
        ->and($fine->getAmount()->getAmount())->toBe(3000.0)
        ->and((string) $fine)->toBe('Rp 3.000');
});
