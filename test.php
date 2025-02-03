<?php

class test
{
    public array $badcars = [
    'validUser1',       // Valid
    'valid_user_2',     // Valid
    'valid.user.3',     // Valid
    'invalid user',     // Invalid (contains space)
    'invalid@user',     // Invalid (contains @)
    'invalid#user',     // Invalid (contains #)
    'valid-user-4',     // Valid
    'invalid$user',     // Invalid (contains $)
    'validUser5',       // Valid
    'invalid%user'      // Invalid (contains %)
];


    static function filterDisallowedCharacters(string $input): string
    {
        $pattern = '/[^a-zA-Z0-9.,!?-]/';
        return preg_replace($pattern, '', $input);
    }

    static function checkfordissalowed(string $input): bool
    {
        $pattern = '/[^a-zA-Z0-9.,!?-]/';
        return preg_match($pattern, $input);
    }
}

$test = new test();

// Example usage
foreach ($test->badcars as $badcar) {
    echo "===============" . PHP_EOL;
    echo $badcar . PHP_EOL;

    $badcar = test::filterDisallowedCharacters($badcar);
    echo $badcar . PHP_EOL;
}


foreach ($test->badcars as $badcar) {
    echo "===============" . PHP_EOL;
    echo $badcar . PHP_EOL;
    $test::checkfordissalowed($badcar) ? print("Invalid") : print("Valid");
    echo PHP_EOL;
}