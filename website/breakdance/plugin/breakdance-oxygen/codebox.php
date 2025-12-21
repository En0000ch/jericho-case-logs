<?php

// @psalm-ignore-file

namespace OxygenWPCodeBox;

// API for WPCodeBox To build
/**
 * @param string $code
 * @return int
 */
function saveSnippetToCodeBox($code)
{
    return \BreakdanceWPCodeBox\saveSnippetToCodeBox($code);
}

/**
 * @param int $id
 */
function runSnippet($id)
{
    \BreakdanceWPCodeBox\runSnippet($id);
}
