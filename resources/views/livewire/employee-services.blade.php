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

    {{-- Responsive Services Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        @if(count($servicesSummary) > 0)
            <div class="min-w-[700px] space-y-1">
                <div class="flex flex-row items-center bg-gray-100 border border-gray-200 rounded text-xs px-2 py-1 font-semibold uppercase tracking-wide text-gray-600">
                    <div class="w-20 min-w-[70px] text-center">Status</div>
                    <div class="w-28 min-w-[100px] text-center">Date</div>
                    <div class="w-40 min-w-[120px] text-center">Service</div>
                    <div class="w-24 min-w-[90px] text-right">Value</div>
                    <div class="w-16 min-w-[60px] text-center">Team</div>
                    <div class="flex-1 min-w-[120px] text-left">Co-workers</div>
                    <div class="flex-1 min-w-[160px] text-left">Recent</div>
                </div>
                @foreach($servicesSummary as $summary)
                    <div class="flex flex-row items-center bg-white border border-gray-200 rounded hover:bg-gray-50 text-xs px-2 py-1">
                        <div class="w-20 min-w-[70px] text-center">
                            @php $isPaid = ($summary['payment_status'] ?? 'unpaid') === 'paid'; @endphp
                            <button wire:click="togglePaidStatus({{ $summary['service']->id }})"
                                class="px-2 py-1 rounded text-xs font-semibold focus:outline-none transition
                                    {{ $isPaid ? 'bg-green-100 text-green-800 border border-green-300 hover:bg-green-200' : 'bg-red-100 text-red-800 border border-red-300 hover:bg-red-200' }}">
                                {{ $isPaid ? 'Paid' : 'Unpaid' }}
                            </button>
                        </div>
                        <div class="w-28 min-w-[100px] text-center font-semibold text-gray-900">{{ $summary['assignments']->min('created_at')->format('M d, Y') }}</div>
                        <div class="w-40 min-w-[120px] text-center font-semibold text-gray-900">{{ $summary['service']->name }}</div>
                        <div class="w-24 min-w-[90px] text-right font-semibold text-orange-700">₱{{ number_format($summary['serviceValue']) }}</div>
                        <div class="w-16 min-w-[60px] text-center text-gray-900">{{ $summary['otherEmployeesCount'] }}</div>
                        <div class="flex-1 min-w-[120px] text-left">
                            @if($summary['otherEmployeesCount'] > 0)
                                @foreach($summary['otherEmployees'] as $coworker)
                                    <span class="inline-block bg-blue-100 text-blue-800 rounded px-1 py-0.5 mr-1 mb-1">
                                        {{ $coworker['name'] }}<span class="ml-0.5 font-semibold">{{ $coworker['count'] }}×</span>
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-[160px] text-left">
                            @php $recent = $summary['assignments']->take(2); @endphp
                            @if($recent->count() > 0)
                                @foreach($recent as $assignment)
                                    <div class="inline-block border border-gray-200 rounded px-1 py-0.5 mr-1 mb-1">
                                        <span class="font-semibold">ORD-{{ str_pad($assignment->order->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-gray-500">{{ $assignment->order->customer_name ?? 'N/A' }}</span>
                                        <span class="ml-1 px-1 rounded bg-orange-100 text-orange-800 text-[10px]">{{ ucfirst(str_replace('_', ' ', $assignment->order->status)) }}</span>
                                        <a href="{{route('orders.view', $assignment->order->id) }}"
                                            class="ml-2 inline-flex items-center text-blue-600 hover:underline text-xs font-medium"
                                            target="_blank" title="View Order">
                                            View
                                        </a>
                                    </div>
                                @endforeach
                                @if($summary['assignments']->count() > 2)
                                    <span class="text-[10px] text-gray-500">+{{ $summary['assignments']->count() - 2 }} more</span>
                                @endif
                            @else
                                <span class="text-gray-400">No recent</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
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
</div>
