<x-filament-panels::page>
    <h2 class="text-2xl font-bold tracking-tight">Cloud Details</h2>
    <div class="mt-4 space-y-2">
        <div class="flex items-center gap-2">
            <strong class="font-semibold">Name:</strong>
            <span>{{ $this->getRecord()->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <strong class="font-semibold">User:</strong>
            <span>{{ $this->getRecord()->user->name }}</span>
        </div>
    </div>

    <h3 class="mt-6 text-xl font-bold tracking-tight">Subscriptions</h3>
    <div class="mt-4">
        @if($subscriptions->isNotEmpty())
            <x-filament::card>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Sites</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Total Users</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Expired At</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Created At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->title }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->type }}</td>
                                <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                            {{ $subscription->status }}
                                        </span>
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ count($subscription->sites ?? []) }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->total_users }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->expire_date}}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @if(!empty($subscription->sites))
                                <tr>
                                    <td colspan="7" class="px-4 py-4">
                                        <div class="p-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sites for {{ $subscription->title }}</h4>
                                            <div class="mt-2 overflow-x-auto">
                                                <table class="w-full border-collapse text-sm">
                                                    <thead>
                                                    <tr class="bg-gray-50 dark:bg-gray-900">
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">URL</th>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($subscription->sites as $index => $site)
                                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                                                <a href="{{ is_array($site) ? $site['url'] : $site }}" target="_blank" class="text-primary-600 hover:underline dark:text-primary-400">
                                                                    {{ is_array($site) ? $site['url'] : $site }}
                                                                </a>
                                                            </td>
                                                            <td class="px-4 py-3">
{{--                                                                <x-filament::action--}}
{{--                                                                    label="Remove"--}}
{{--                                                                    color="danger"--}}
{{--                                                                    icon="heroicon-o-trash"--}}
{{--                                                                    :action="fn() => $this->removeSite($subscription->id, $index)"--}}
{{--                                                                    requires-confirmation--}}
{{--                                                                    confirmation-message="Are you sure you want to remove this site URL?"--}}
{{--                                                                />--}}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-gray-500 dark:text-gray-400">No sites found for this subscription.</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        @else
            <x-filament::card>
                <p class="text-gray-500 dark:text-gray-400">No subscriptions found for this user.</p>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
