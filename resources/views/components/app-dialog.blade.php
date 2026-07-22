<dialog id="app-dialog" class="app-dialog">
    <form method="dialog" class="flex flex-col gap-4 p-6">
        <p id="app-dialog-message" class="text-sm text-gray-700 dark:text-gray-200"></p>

        <input
            id="app-dialog-input"
            type="text"
            class="form-input-admin hidden"
            autocomplete="off"
        >

        <div class="flex justify-end gap-2">
            <button
                type="button"
                id="app-dialog-cancel"
                class="admin-btn admin-btn-ghost hidden"
            >
                {{ __('Cancel') }}
            </button>
            <button
                type="submit"
                id="app-dialog-ok"
                value="ok"
                class="admin-btn admin-btn-primary"
            >
                <span id="app-dialog-ok-label">{{ __('OK') }}</span>
            </button>
        </div>
    </form>
</dialog>
