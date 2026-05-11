<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Support Workflow Demo</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased">
        <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-5 py-6 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-2 border-b border-zinc-200 pb-5">
                <p class="text-sm font-medium text-zinc-500">AI-powered support workflow</p>
                <h1 class="text-3xl font-semibold">Support demo console</h1>
                <p class="max-w-3xl text-sm leading-6 text-zinc-600">
                    Choose a prepared customer ticket, run the support workflow, and inspect the structured triage, suggested reply, and customer lookup tool activity.
                </p>
            </header>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <form method="POST" action="{{ route('support-demo.triage') }}" class="flex flex-col gap-4">
                    @csrf

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Demo tickets</h2>
                            <p class="text-sm text-zinc-500">Four examples from the eval walkthrough.</p>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2">
                            Triage
                        </button>
                    </div>

                    @error('ticket_id')
                        <p class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror

                    <div class="grid gap-3">
                        @foreach ($tickets as $ticket)
                            @php
                                $isSelected = old('ticket_id', $selectedTicketId ?? $tickets[0]['id']) === $ticket['id'];
                            @endphp

                            <label class="ticket-option block cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-400">
                                <input type="radio" name="ticket_id" value="{{ $ticket['id'] }}" class="sr-only" @checked($isSelected)>

                                <span class="flex flex-wrap items-start justify-between gap-3">
                                    <span>
                                        <span class="block text-sm font-semibold">{{ $ticket['label'] }}</span>
                                        <span class="block text-xs text-zinc-500">{{ $ticket['customer_identifier'] }}</span>
                                    </span>
                                </span>

                                <span class="mt-3 block text-sm leading-6 text-zinc-700">{{ $ticket['message'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </form>

                <section class="flex flex-col gap-4">
                    @if ($error !== null)
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-zinc-500">Selected ticket</p>
                                    <h2 class="mt-1 text-xl font-semibold">{{ $error['ticket']['label'] }}</h2>
                                </div>
                                <span class="rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">{{ $error['ticket']['customer_identifier'] }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-zinc-700">{{ $error['ticket']['message'] }}</p>
                        </div>

                        <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-red-950">Workflow did not complete</h2>
                            <p class="mt-2 text-sm leading-6 text-red-800">{{ $error['message'] }}</p>
                            @if ($error['detail'] !== null)
                                <pre class="mt-4 overflow-auto rounded-md bg-red-950 p-3 text-xs text-red-50">{{ $error['detail'] }}</pre>
                            @endif
                        </div>
                    @elseif ($result === null)
                        <div class="rounded-lg border border-dashed border-zinc-300 bg-white p-8 text-center shadow-sm">
                            <h2 class="text-lg font-semibold">Awaiting triage</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">Pick a ticket and submit it to render the agent output and tool activity here.</p>
                        </div>
                    @else
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-zinc-500">Selected ticket</p>
                                    <h2 class="mt-1 text-xl font-semibold">{{ $result['ticket']['label'] }}</h2>
                                </div>
                                <span class="rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">{{ $result['ticket']['customer_identifier'] }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-zinc-700">{{ $result['ticket']['message'] }}</p>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="text-lg font-semibold">Structured triage</h2>
                            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($result['triage'] as $field => $value)
                                    <div class="rounded-md border border-zinc-200 p-3">
                                        <dt class="text-xs font-medium uppercase text-zinc-500">{{ str_replace('_', ' ', $field) }}</dt>
                                        <dd class="mt-1 text-sm font-medium text-zinc-900">
                                            @if (is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="text-lg font-semibold">Suggested reply</h2>
                            <div class="mt-4 whitespace-pre-line rounded-md border border-zinc-200 bg-zinc-50 p-4 text-sm leading-6 text-zinc-800">
                                {{ $result['reply'] }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold">Tool use</h2>
                                    <p class="text-sm text-zinc-500">Customer lookup status and captured tool payloads.</p>
                                </div>
                                <span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $result['customer_lookup_was_used'] ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-700' }}">
                                    CustomerLookupTool used: {{ $result['customer_lookup_was_used'] ? 'Yes' : 'No' }}
                                </span>
                            </div>

                            @if (count($result['tool_calls']) === 0 && count($result['tool_results']) === 0)
                                <p class="mt-4 rounded-md border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-500">No tools were called for this ticket.</p>
                            @else
                                <div class="mt-4 grid gap-4">
                                    @foreach ($result['tool_calls'] as $toolCall)
                                        <div class="rounded-md border border-zinc-200 p-4">
                                            <h3 class="text-sm font-semibold">Call: {{ $toolCall['name'] ?? 'Unknown tool' }}</h3>
                                            <dl class="mt-3 grid gap-2 text-sm">
                                                @foreach (($toolCall['arguments'] ?? []) as $name => $value)
                                                    <div class="grid gap-1 sm:grid-cols-[12rem_1fr]">
                                                        <dt class="font-medium text-zinc-500">{{ str_replace('_', ' ', $name) }}</dt>
                                                        <dd class="text-zinc-800">{{ is_scalar($value) || $value === null ? ($value ?? 'null') : json_encode($value) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    @endforeach

                                    @foreach ($result['tool_results'] as $toolResult)
                                        <div class="rounded-md border border-zinc-200 p-4">
                                            <h3 class="text-sm font-semibold">Result: {{ $toolResult['name'] ?? 'Unknown tool' }}</h3>

                                            @if (is_array($toolResult['decoded_result'] ?? null))
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    @foreach ($toolResult['decoded_result'] as $name => $value)
                                                        <div class="grid gap-1 sm:grid-cols-[12rem_1fr]">
                                                            <dt class="font-medium text-zinc-500">{{ str_replace('_', ' ', $name) }}</dt>
                                                            <dd class="text-zinc-800">
                                                                @if (is_bool($value))
                                                                    {{ $value ? 'true' : 'false' }}
                                                                @elseif ($value === null)
                                                                    null
                                                                @else
                                                                    {{ is_scalar($value) ? $value : json_encode($value) }}
                                                                @endif
                                                            </dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            @else
                                                <pre class="mt-3 overflow-auto rounded-md bg-zinc-950 p-3 text-xs text-zinc-50">{{ $toolResult['result'] ?? '' }}</pre>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="text-lg font-semibold">Run metadata</h2>
                            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-md border border-zinc-200 p-3">
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Triage model</dt>
                                    <dd class="mt-1 text-sm text-zinc-800">{{ $result['meta']['triage']['provider'] ?? 'unknown' }} / {{ $result['meta']['triage']['model'] ?? 'unknown' }}</dd>
                                </div>
                                <div class="rounded-md border border-zinc-200 p-3">
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Reply model</dt>
                                    <dd class="mt-1 text-sm text-zinc-800">{{ $result['meta']['reply']['provider'] ?? 'unknown' }} / {{ $result['meta']['reply']['model'] ?? 'unknown' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </section>
            </section>
        </main>
    </body>
</html>
