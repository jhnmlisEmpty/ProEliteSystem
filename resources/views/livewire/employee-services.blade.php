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
        @if(count($assignmentsByDate) > 0)
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b-2 border-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Date</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Service</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-800 uppercase tracking-wide">Value</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-800 uppercase tracking-wide">Team</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Co-workers</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-800 uppercase tracking-wide">Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($assignmentsByDate as $date => $assignments)
                        @php
                            $isFirstRow = true;
                            $rowCount = count($assignments);
                        @endphp
                        @foreach($assignments as $assignment)
                            @php
                                // Get the value of this specific assignment from order items
                                $itemValue = \App\Models\OrderItem::where('order_id', $assignment->order_id)
                                    ->where('service_id', $assignment->service_id)
                                    ->sum('total_price');
                                $isPaid = ($assignment->payment_status ?? 'unpaid') === 'paid';
                                
                                // Get other employees assigned to this service
                                $otherEmployees = \App\Models\ServiceAssignment::where('service_id', $assignment->service_id)
                                    ->where('employee_id', '!=', $this->employee->id)
                                    ->with('employee')
                                    ->get()
                                    ->groupBy('employee_id')
                                    ->map(function ($group) {
                                        return [
                                            'name' => $group->first()->employee->name,
                                            'count' => $group->count()
                                        ];
                                    })
                                    ->values();
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                {{-- Date (only on first row of each date group) --}}
                                @if($isFirstRow)
                                    <td class="px-6 py-4 text-gray-900 font-semibold align-middle" rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('M d, Y') }}
                                    </td>
                                @endif
                                
                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    <button wire:click="togglePaidStatus({{ $assignment->id }})"
                                        class="px-2 py-1 rounded text-xs font-semibold focus:outline-none transition
                                            {{ $isPaid ? 'bg-green-100 text-green-800 border border-green-300 hover:bg-green-200' : 'bg-red-100 text-red-800 border border-red-300 hover:bg-red-200' }}">
                                        {{ $isPaid ? 'Paid' : 'Unpaid' }}
                                    </button>
                                </td>
                                
                                {{-- Service --}}
                                <td class="px-6 py-4 text-gray-900 font-semibold">{{ $assignment->service->name }}</td>
                                
                                {{-- Value --}}
                                <td class="px-6 py-4 text-right text-gray-900 font-semibold text-orange-700">₱{{ number_format($itemValue) }}</td>
                                
                                {{-- Team Count --}}
                                <td class="px-6 py-4 text-center text-gray-900">{{ $otherEmployees->count() }}</td>
                                
                                {{-- Co-workers --}}
                                <td class="px-6 py-4 text-gray-700">
                                    @if($otherEmployees->count() > 0)
                                        @foreach($otherEmployees as $coworker)
                                            <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-1 mr-1 mb-1 text-xs">
                                                {{ $coworker['name'] }}<span class="ml-1 font-semibold">{{ $coworker['count'] }}×</span>
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                
                                {{-- Order --}}
                                <td class="px-6 py-4 text-gray-700">
                                    <div class="inline-block border border-gray-200 rounded px-2 py-1">
                                        <span class="font-semibold">ORD-{{ str_pad($assignment->order->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-gray-500 ml-1">{{ $assignment->order->customer_name ?? 'N/A' }}</span>
                                        <span class="ml-1 px-1 rounded bg-orange-100 text-orange-800 text-[10px]">{{ ucfirst(str_replace('_', ' ', $assignment->order->status)) }}</span>
                                        <a href="{{route('orders.view', $assignment->order->id) }}"
                                            class="ml-2 inline-flex items-center text-blue-600 hover:underline text-xs font-medium"
                                            target="_blank" title="View Order">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @php $isFirstRow = false; @endphp
                        @endforeach
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
</div>
