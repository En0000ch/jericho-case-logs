<?php

/**
 * @param string $tag The name of the action to be executed.
 * @param mixed  ...$args Optional additional arguments which are passed on to the functions hooked to the action.
 * @return void
 */
function bdox_run_action($tag, ...$args)
{
    do_action($tag, ...$args);
    $oxygen_tag = str_replace('breakdance_', 'oxygen_', $tag);
    do_action($oxygen_tag, ...$args);
}

/**
 * @param string $tag The name of the filter hook.
 * @param mixed  $value The value to filter.
 * @param mixed  ...$args Additional arguments to pass to the functions hooked to the filter.
 * @return mixed The filtered value after all associated hooks have applied their modifications.
 */
function bdox_run_filters($tag, $value, ...$args)
{
    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress TooManyArguments
     */
    $result = apply_filters($tag, $value, ...$args);

    $oxygen_tag = str_replace('breakdance_', 'oxygen_', $tag);

    /** @psalm-suppress TooManyArguments */
    return apply_filters($oxygen_tag, $result, ...$args);
}
