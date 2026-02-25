<?php
$content = file_get_contents($argv[1]);
$lines = explode("\n", $content);
$stack = [];
$errors = [];

foreach ($lines as $i => $line) {
    $ln = $i + 1;
    // Simple regex for directives. This is not perfect for nested on one line but usually DASHBOARD is clear.
    if (preg_match('/(@if|@foreach|@forelse)\b/', $line, $m)) {
        $stack[] = ['type' => $m[1], 'line' => $ln];
    }
    if (preg_match('/@elseif\b/', $line)) {
        if (empty($stack) || $stack[count($stack) - 1]['type'] !== '@if') {
            $errors[] = "Misplaced @elseif at line $ln";
        }
    }
    if (preg_match('/@else\b/', $line)) {
        // Part of @if or @forelse (empty)
        if (empty($stack) || !in_array($stack[count($stack) - 1]['type'], ['@if', '@forelse'])) {
            // Note: @else is only for @if or part of @forelse as @empty? No, @else is not for forelse. @empty is.
            // But sometimes people use @else inside @forelse? No.
            // Wait, is @else allowed in @if only? Yes.
            if ($stack[count($stack) - 1]['type'] !== '@if') {
                $errors[] = "Misplaced @else at line $ln";
            }
        }
    }
    if (preg_match('/@endif\b/', $line)) {
        if (empty($stack) || $stack[count($stack) - 1]['type'] !== '@if') {
            $errors[] = "Unmatched @endif at line $ln";
        } else {
            array_pop($stack);
        }
    }
    if (preg_match('/@endforeach\b/', $line)) {
        if (empty($stack) || $stack[count($stack) - 1]['type'] !== '@foreach') {
            $errors[] = "Unmatched @endforeach at line $ln";
        } else {
            array_pop($stack);
        }
    }
    if (preg_match('/@endforelse\b/', $line)) {
        if (empty($stack) || $stack[count($stack) - 1]['type'] !== '@forelse') {
            $errors[] = "Unmatched @endforelse at line $ln";
        } else {
            array_pop($stack);
        }
    }
}

foreach ($stack as $item) {
    $errors[] = "Unclosed {$item['type']} starting at line {$item['line']}";
}

if (empty($errors)) {
    echo "No directive mismatch found.\n";
} else {
    echo implode("\n", $errors) . "\n";
}
