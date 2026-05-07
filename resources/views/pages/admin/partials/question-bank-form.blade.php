@php
    $isCurrentModal = old('question_modal') === $modalKey;
    $defaultCategory = $defaultCategoryId ?? ($categoryOptions[0]['id'] ?? 'job');
    $selectedCategory = $isCurrentModal ? old('category_id') : ($question['categoryId'] ?? $defaultCategory);
    $selectedProvider = $isCurrentModal ? old('provider_id') : ($question['providerId'] ?? 'local');
    $questionText = $isCurrentModal ? old('question') : ($question['question'] ?? '');
    $guidanceText = $isCurrentModal ? old('guidance') : ($question['guidance'] ?? '');
    $sortOrder = $isCurrentModal ? old('sort_order') : ($question['sortOrder'] ?? 0);
    $isActive = $isCurrentModal ? ((string) old('is_active', '0') === '1') : (bool) ($question['isActive'] ?? true);
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    <label class="space-y-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Category</span>
        <select name="category_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-800" required>
            @foreach ($categoryOptions as $category)
                <option value="{{ $category['id'] }}" @selected($selectedCategory === $category['id'])>{{ $category['name'] }}</option>
            @endforeach
        </select>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Source</span>
        <select name="provider_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-800" required>
            @foreach ($providerOptions as $provider)
                <option value="{{ $provider['id'] }}" @selected($selectedProvider === $provider['id'])>
                    {{ $provider['label'] }} - {{ $provider['typeLabel'] }}{{ ! $provider['configured'] ? ' source' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Order</span>
        <input type="number" name="sort_order" min="0" max="999" value="{{ $sortOrder }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required />
    </label>
</div>

<label class="block space-y-2">
    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Question</span>
    <textarea name="question" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required>{{ $questionText }}</textarea>
</label>

<label class="block space-y-2">
    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Coach guidance</span>
    <textarea name="guidance" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800">{{ $guidanceText }}</textarea>
</label>

<div class="flex flex-wrap items-center justify-between gap-4">
    <label class="inline-flex items-center gap-3">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked($isActive) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Active in coach context</span>
    </label>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <button type="button" @click="questionModal = null" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Save question</button>
    </div>
</div>
