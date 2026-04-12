<div class="mx-auto mb-10 w-full max-w-60 rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]">
    <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
        Admin Control Center
    </h3>
    <p class="mb-4 text-gray-500 text-theme-sm dark:text-gray-400">
        Review member access, monitor activity totals, and keep administrator access under control.
    </p>
    <div class="space-y-2">
        <a href="{{ route('admin.users') }}"
            class="flex items-center justify-center rounded-lg bg-warning-500 p-3 font-medium text-white text-theme-sm hover:bg-warning-600">
            Manage Users
        </a>
        <a href="{{ route('admin.apis') }}"
            class="flex items-center justify-center rounded-lg border border-gray-300 bg-white p-3 font-medium text-gray-700 text-theme-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            Manage APIs
        </a>
    </div>
</div>
