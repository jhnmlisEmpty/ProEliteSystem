<div>
    {{-- Page Header --}}
    <x-page-header title="{{ $user->name }}'s Services" subtitle="Assignments and service value from order items">
        <x-slot name="actions">
            <a href="{{ route('users.index') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-lg shadow-sm transition">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Back to Users
            </a>
        </x-slot>
    </x-page-header>

    {{-- Filters Section --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- From Date --}}
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input wire:model.live="startDate" 
                       type="date" 
                       id="startDate"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- To Date --}}
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input wire:model.live="endDate" 
                       type="date" 
                       id="endDate"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Buttons --}}
            <div class="flex items-end gap-2">
                <button wire:click="$refresh" 
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                    Apply
                </button>
                <button wire:click="clearFilters" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-lg transition">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Services Table (Desktop) --}}
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        @if(count($servicesSummary) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value (order items)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Team Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Co-workers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recent Assignments</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($servicesSummary as $summary)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 align-middle text-center">
                                <div class="font-semibold text-gray-900">{{ $summary['service']->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-right align-middle font-semibold text-orange-700">₱{{ number_format($summary['serviceValue']) }}</td>
                            <td class="px-6 py-4 text-center align-middle text-gray-900">{{ $summary['otherEmployeesCount'] }}</td>
                            <td class="px-6 py-4 align-middle text-center">
                                @if($summary['otherEmployeesCount'] > 0)
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach($summary['otherEmployees'] as $coworker)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $coworker['name'] }}
                                                <span class="ml-1 font-semibold">{{ $coworker['count'] }}×</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle text-center">
                                @php $recent = $summary['assignments']->take(2); @endphp
                                @if($recent->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($recent as $assignment)
                                            <a href="{{ route('orders.view', $assignment->order->id) }}" wire:navigate
                                               class="flex items-center justify-center text-xs bg-white border border-gray-200 rounded px-3 py-2 hover:bg-gray-50 transition">
                                                <div class="text-center">
                                                    <div class="font-semibold text-gray-900">ORD-{{ str_pad($assignment->order->id, 3, '0', STR_PAD_LEFT) }}</div>
                                                    <div class="text-gray-600">{{ $assignment->order->customer_name ?? 'N/A' }} • {{ $assignment->created_at->format('M d, Y') }}</div>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800 ml-2">
                                                    {{ ucfirst(str_replace('_', ' ', $assignment->order->status)) }}
                                                </span>
                                            </a>
                                        @endforeach
                                        @if($summary['assignments']->count() > 2)
                                            <div class="text-[11px] text-gray-600 text-center">+{{ $summary['assignments']->count() - 2 }} more</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">No recent orders</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900">No Services Assigned Yet</h3>
                <p class="text-gray-600 mt-1">This employee hasn't been assigned to any services yet.</p>
            </div>
        @endif
    </div>

    {{-- Services Grid (Mobile) --}}
    <div class="md:hidden space-y-4">
        @forelse($servicesSummary as $summary)
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $summary['service']->name }}</h3>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase">Service Value</p>
                        <p class="text-lg font-semibold text-orange-700">₱{{ number_format($summary['serviceValue']) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm mb-4 pb-4 border-b border-gray-200">
                    <div>
                        <span class="text-gray-500 block mb-1">Team Members</span>
                        <span class="font-semibold text-gray-900 text-lg">{{ $summary['otherEmployeesCount'] }}</span>
                    </div>
                </div>

                @if($summary['otherEmployeesCount'] > 0)
                    <div class="mb-4">
                        <p class="text-xs text-gray-600 uppercase mb-2 font-semibold">Co-workers</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($summary['otherEmployees'] as $coworker)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $coworker['name'] }}
                                    <span class="ml-1 font-semibold">{{ $coworker['count'] }}×</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <p class="text-xs text-gray-600 uppercase mb-2 font-semibold">Recent Assignments</p>
                    @php $recent = $summary['assignments']->take(2); @endphp
                    @if($recent->count() > 0)
                        <div class="space-y-2">
                            @foreach($recent as $assignment)
                                <a href="{{ route('orders.view', $assignment->order->id) }}" wire:navigate
                                   class="text-xs bg-gray-50 border border-gray-200 rounded p-2 block hover:bg-gray-100 transition">
                                    <div class="font-semibold text-gray-900">ORD-{{ str_pad($assignment->order->id, 3, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-gray-600">{{ $assignment->order->customer_name ?? 'N/A' }} • {{ $assignment->created_at->format('M d, Y') }}</div>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->order->status)) }}
                                    </span>
                                </a>
                            @endforeach
                            @if($summary['assignments']->count() > 2)
                                <div class="text-[11px] text-gray-600">+{{ $summary['assignments']->count() - 2 }} more</div>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-gray-500">No recent orders</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center border border-gray-200">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900">No Services Assigned Yet</h3>
                <p class="text-gray-600 mt-1">This employee hasn't been assigned to any services yet.</p>
            </div>
        @endforelse
    </div>
</div>
