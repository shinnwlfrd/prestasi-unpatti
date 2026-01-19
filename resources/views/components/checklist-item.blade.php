@props(['name', 'label', 'checked' => false, 'notes' => ''])

<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
    <div class="flex items-start gap-3">
        <div class="flex items-center h-6">
            <input 
                type="checkbox" 
                name="checklist[{{ $name }}_valid]" 
                id="{{ $name }}_valid"
                value="1"
                {{ $checked ? 'checked' : '' }}
                class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer"
                @change="updateProgress()"
            >
        </div>
        <div class="flex-1">
            <label for="{{ $name }}_valid" class="font-medium text-gray-900 dark:text-white cursor-pointer">
                {{ $label }}
            </label>
            <div class="mt-2">
                <input 
                    type="text" 
                    name="checklist[{{ $name }}_notes]" 
                    placeholder="Catatan (opsional)"
                    value="{{ $notes }}"
                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500"
                >
            </div>
        </div>
    </div>
</div>
