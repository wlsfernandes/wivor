<x-email.layout :title="$heading">
    <h1>{{ $heading }}</h1>
    <p>Hello {{ $user->name }},</p>
    <p>{{ $messageText }}</p>
    <p>No action will ever require you to send your password by email.</p>
</x-email.layout>
